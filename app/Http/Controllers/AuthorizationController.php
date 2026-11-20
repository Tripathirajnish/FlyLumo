<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthorizationController extends Controller
{
    private function guard(Request $r)
    {
        return $r->session()->has('crm_admin_id') ? null : redirect()->route('crm.login');
    }

    public function booking(Request $r, int $id)
    {
        if ($x=$this->guard($r)) return $x;
        $booking=Booking::findOrFail($id);
        $items=DB::table('booking_authorizations')->where('booking_id',$id)->orderByDesc('id')->get();
        return view('crm.authorization-center',compact('booking','items'));
    }

    public function create(Request $r, int $id)
    {
        if ($x=$this->guard($r)) return $x;

        $b=Booking::findOrFail($id);
        $d=$r->validate([
            'amount'=>'required|numeric|min:0.01',
            'currency'=>'required|string|max:8',
            'expires_days'=>'required|integer|min:1|max:30',
        ]);

        if (!filter_var((string)$b->o_Email,FILTER_VALIDATE_EMAIL)) {
            return back()->with('crm_error','Booking does not have a valid passenger email address.');
        }

        $token=Str::random(64);
        $snap=$this->bookingSnapshot($b);

        $idAuth=DB::table('booking_authorizations')->insertGetId([
            'booking_id'=>$id,
            'admin_id'=>$r->session()->get('crm_admin_id'),
            'token_hash'=>hash('sha256',$token),
            'token_encrypted'=>Crypt::encryptString($token),
            'recipient_email'=>$b->o_Email,
            'status'=>'sent',
            'authorized_amount'=>$d['amount'],
            'currency'=>strtoupper($d['currency']),
            'itinerary_snapshot'=>json_encode($snap,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            'expires_at'=>now()->addDays($d['expires_days']),
            'created_at'=>now(),
            'updated_at'=>now(),
        ]);

        $a=DB::table('booking_authorizations')->find($idAuth);
        $url=$this->publicUrl($token);
        $sent=$this->sendRequestEmail($b,$a,$url);

        DB::table('booking_authorizations')->where('id',$idAuth)->update([
            'emailed_at'=>$sent ? now() : null,
            'email_status'=>$sent ? 'sent' : 'failed',
            'updated_at'=>now(),
        ]);

        $this->emailLog($r,$b,'authorization_request',$sent,$sent?null:'SMTP sender returned false.');

        if ($sent) {
            return back()->with('crm_success','Secure authorization link created and emailed to '.$b->o_Email.'.');
        }

        return back()
            ->with('crm_success','Authorization created. Secure link: '.$url)
            ->with('crm_error','Email could not be sent. You can copy the secure link from the authorization row.');
    }

    public function resend(Request $r, int $id)
    {
        if ($x=$this->guard($r)) return $x;

        $a=DB::table('booking_authorizations')->find($id);
        abort_unless($a,404);
        $b=Booking::findOrFail($a->booking_id);

        if ($a->status==='completed' || $a->revoked_at || ($a->expires_at && now()->gt($a->expires_at))) {
            return back()->with('crm_error','This authorization cannot be resent because it is completed, revoked or expired.');
        }
        if (!filter_var((string)$b->o_Email,FILTER_VALIDATE_EMAIL)) {
            return back()->with('crm_error','Booking does not have a valid passenger email address.');
        }
        if (empty($a->token_encrypted)) {
            return back()->with('crm_error','This older authorization does not contain a reusable secure token. Create a new authorization.');
        }

        try {
            $token=Crypt::decryptString($a->token_encrypted);
        } catch (\Throwable $e) {
            return back()->with('crm_error','Secure token could not be restored. Create a new authorization.');
        }

        $url=$this->publicUrl($token);
        $sent=$this->sendRequestEmail($b,$a,$url);

        DB::table('booking_authorizations')->where('id',$id)->update([
            'recipient_email'=>$b->o_Email,
            'emailed_at'=>$sent ? now() : $a->emailed_at,
            'email_status'=>$sent ? 'sent' : 'failed',
            'updated_at'=>now(),
        ]);

        $this->emailLog($r,$b,'authorization_resend',$sent,$sent?null:'SMTP sender returned false.');

        return $sent
            ? back()->with('crm_success','Authorization email resent to '.$b->o_Email.'.')
            : back()->with('crm_error','Authorization email could not be sent.');
    }

    public function revoke(Request $r, int $id)
    {
        if($x=$this->guard($r)) return $x;
        DB::table('booking_authorizations')->where('id',$id)->update([
            'status'=>'revoked','revoked_at'=>now(),'updated_at'=>now()
        ]);
        return back()->with('crm_success','Authorization link revoked.');
    }

    public function publicForm(Request $r, string $token)
    {
        $a=$this->find($token);
        abort_unless($a,404);

        // Completed link: show the confirmation page, not the booking/itinerary.
        if($a->status==='completed') {
            return view('authorization.complete',compact('a'));
        }

        // Revoked or expired link: reject before any booking data is loaded
        // or the link is marked as viewed. Same response as submit().
        abort_if($a->revoked_at || ($a->expires_at && now()->gt($a->expires_at)),410);

        if(!$a->viewed_at) {
            DB::table('booking_authorizations')->where('id',$a->id)->update([
                'viewed_at'=>now(),
                'status'=>$a->status==='sent'?'viewed':$a->status,
                'updated_at'=>now(),
            ]);
            $a=DB::table('booking_authorizations')->find($a->id);
        }

        $b=Booking::findOrFail($a->booking_id);
        return view('authorization.form',compact('a','b','token'));
    }

    public function submit(Request $r, string $token)
    {
        $a=$this->find($token);
        abort_unless($a,404);
        abort_if($a->revoked_at || $a->status==='completed' || ($a->expires_at && now()->gt($a->expires_at)),410);

        $d=$r->validate([
            'signer_name'=>'required|string|max:150',
            'cardholder_name'=>'required|string|max:150',
            'card_brand'=>'required|string|max:50',
            'card_last4'=>'required|digits:4',
            'card_expiry_month'=>'required|digits:2|between:1,12',
            'card_expiry_year'=>'required|integer|min:'.date('Y').'|max:'.((int)date('Y')+15),
            'signature'=>'required|string|max:2000000',
            'consent_amount'=>'accepted',
            'consent_itinerary'=>'accepted',
            'consent_terms'=>'accepted',
            'documents.*'=>'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        // Atomic claim: the row is only updated if it is still open at the
        // moment of the write. Concurrent submissions with the same token
        // cannot both pass — exactly one update affects the row.
        $claimed=DB::table('booking_authorizations')
            ->where('id',$a->id)
            ->whereNull('revoked_at')
            ->where(function($q){ $q->whereNull('status')->orWhere('status','!=','completed'); })
            ->where(function($q){ $q->whereNull('expires_at')->orWhere('expires_at','>',now()); })
            ->update([
            'status'=>'completed',
            'cardholder_name'=>$d['cardholder_name'],
            'card_brand'=>$d['card_brand'],
            'card_last4'=>$d['card_last4'],
            'card_expiry_month'=>str_pad((string)$d['card_expiry_month'],2,'0',STR_PAD_LEFT),
            'card_expiry_year'=>(string)$d['card_expiry_year'],
            'consent_json'=>json_encode(['amount'=>true,'itinerary'=>true,'terms'=>true]),
            'signature_data'=>$d['signature'],
            'signer_name'=>$d['signer_name'],
            'signer_ip'=>$r->ip(),
            'user_agent'=>substr((string)$r->userAgent(),0,2000),
            'completed_at'=>now(),
            'updated_at'=>now(),
        ]);

        if($claimed!==1) {
            // Another request completed (or the link was revoked/expired) first.
            // No documents are stored and no email is sent for this request.
            $current=DB::table('booking_authorizations')->find($a->id);
            if($current && $current->status==='completed') {
                return view('authorization.complete',['a'=>$current]);
            }
            abort(410);
        }

        foreach($r->file('documents',[]) as $f) {
            $path=$f->store('private/authorizations/'.$a->id);
            DB::table('booking_authorization_documents')->insert([
                'authorization_id'=>$a->id,
                'original_name'=>$f->getClientOriginalName(),
                'stored_path'=>$path,
                'mime_type'=>$f->getMimeType(),
                'size_bytes'=>$f->getSize(),
                'created_at'=>now(),
                'updated_at'=>now(),
            ]);
        }

        $a=DB::table('booking_authorizations')->find($a->id);
        $b=Booking::findOrFail($a->booking_id);
        $this->sendCompletionEmail($b,$a);

        return view('authorization.complete',compact('a'));
    }

    public function evidence(Request $r, int $id)
    {
        if($x=$this->guard($r)) return $x;
        $a=DB::table('booking_authorizations')->find($id);
        abort_unless($a,404);
        $b=Booking::findOrFail($a->booking_id);
        $docs=DB::table('booking_authorization_documents')->where('authorization_id',$id)->get();
        return view('crm.authorization-evidence',compact('a','b','docs'));
    }

    public function document(Request $r, int $id)
    {
        if($x=$this->guard($r)) return $x;
        $d=DB::table('booking_authorization_documents')->find($id);
        abort_unless($d,404);
        return Storage::download($d->stored_path,$d->original_name);
    }

    private function find(string $token)
    {
        return DB::table('booking_authorizations')->where('token_hash',hash('sha256',$token))->first();
    }

    private function bookingSnapshot(Booking $b): array
    {
        $decode=function($v){
            if(is_array($v)) return $v;
            $d=json_decode((string)$v,true);
            return json_last_error()===JSON_ERROR_NONE && is_array($d) ? $d : [];
        };

        return [
            'booking_reference'=>($b->prefix??'').$b->id,
            'passenger_name'=>$b->o_Name,
            'passengers'=>$decode($b->o_Passenger),
            'sector_go'=>$decode($b->o_SectorGo),
            'sector_back'=>$decode($b->o_SectorBack),
            'captured_at'=>now()->toIso8601String(),
        ];
    }

    private function publicUrl(string $token): string
    {
        $base=rtrim((string)env('AUTHORIZATION_PUBLIC_BASE_URL'),' /');
        return $base !== ''
            ? $base.'/authorization/'.$token
            : route('authorization.public',$token);
    }

    private function sendRequestEmail(Booking $b, object $a, string $url): bool
    {
        $subject='Secure Credit Card Authorization | Fares Hunt | #'.($b->prefix??'').$b->id;
        $amount=e($a->currency.' '.number_format((float)$a->authorized_amount,2));
        $name=e($b->o_Name ?: 'Passenger');
        $ref=e(($b->prefix??'').$b->id);
        $expiry=$a->expires_at ? e(\Carbon\Carbon::parse($a->expires_at)->format('d M Y, h:i A')) : '—';
        $safeUrl=e($url);

        $body='<!doctype html><html><body style="margin:0;background:#f4f7fb;font-family:Arial,sans-serif;color:#10294c">'
            .'<div style="max-width:680px;margin:30px auto;background:#fff;border:1px solid #dfe7f2;border-radius:16px;overflow:hidden">'
            .'<div style="padding:24px 28px;background:linear-gradient(110deg,#e50055,#104ca9);color:#fff">'
            .'<div style="font-size:25px;font-weight:800">Fares Hunt</div><div style="margin-top:5px">Secure Credit Card Authorization</div></div>'
            .'<div style="padding:28px"><p>Hi '.$name.',</p><p>Please review and sign the secure authorization for your booking.</p>'
            .'<div style="background:#f4f8ff;border-radius:12px;padding:16px;margin:20px 0"><b>Booking #'.$ref.'</b><br>Amount: <b>'.$amount.'</b><br>Link expires: '.$expiry.'</div>'
            .'<p style="text-align:center;margin:28px 0"><a href="'.$safeUrl.'" style="display:inline-block;background:#1359d8;color:#fff;text-decoration:none;padding:14px 24px;border-radius:9px;font-weight:bold">Review & Sign Authorization</a></p>'
            .'<p style="font-size:12px;color:#6a7b93">For your security, this form does not request your full card number or CVV. Please do not upload documents containing a full card number or CVV.</p>'
            .'<p>Fares Hunt Support</p></div></div></body></html>';

        return (bool)app(\App\Http\Controllers\FlightController::class)->sendSmptEmail($b->o_Email,$subject,$body);
    }

    private function sendCompletionEmail(Booking $b, object $a): void
    {
        if(!filter_var((string)$b->o_Email,FILTER_VALIDATE_EMAIL)) return;

        $subject='Authorization Received | Fares Hunt | #'.($b->prefix??'').$b->id;
        $body='<div style="font-family:Arial,sans-serif;max-width:650px;margin:auto;color:#10294c">'
            .'<h2 style="color:#0b3f88">Fares Hunt Authorization Received</h2>'
            .'<p>Hi '.e($b->o_Name ?: 'Passenger').',</p>'
            .'<p>Thank you. Your authorization has been securely recorded for booking <b>#'.e(($b->prefix??'').$b->id).'</b>.</p>'
            .'<p><b>Authorized amount:</b> '.e($a->currency).' '.number_format((float)$a->authorized_amount,2).'<br>'
            .'<b>Card:</b> '.e($a->card_brand).' ending '.e($a->card_last4).'<br>'
            .'<b>Completed:</b> '.e((string)$a->completed_at).'</p>'
            .'<p style="font-size:12px;color:#6a7b93">This message is a confirmation of your authorization submission. It is not an airline ticket or boarding pass.</p>'
            .'<p>Fares Hunt Support</p></div>';

        app(\App\Http\Controllers\FlightController::class)->sendSmptEmail($b->o_Email,$subject,$body);
    }

    private function emailLog(Request $r, Booking $b, string $type, bool $sent, ?string $error): void
    {
        if(!Schema::hasTable('crm_email_logs')) return;

        DB::table('crm_email_logs')->insert([
            'booking_id'=>$b->id,
            'admin_id'=>(int)$r->session()->get('crm_admin_id') ?: null,
            'email_type'=>$type,
            'recipient'=>$b->o_Email,
            'subject'=>'Credit Card Authorization',
            'status'=>$sent?'sent':'failed',
            'source'=>'authorization_center',
            'error_message'=>$error,
            'sent_at'=>$sent?now():null,
            'created_at'=>now(),
            'updated_at'=>now(),
        ]);
    }
}