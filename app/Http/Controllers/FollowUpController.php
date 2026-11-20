<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FollowUpController extends Controller
{
    private function guard(Request $request) {
        if (!$request->session()->has('crm_admin_id')) return redirect()->route('crm.login');
        return null;
    }

    public function index(Request $request) {
        if ($r=$this->guard($request)) return $r;
        abort_unless(Schema::hasTable('crm_followups'),500,'Run CRM Follow-ups V2 migration.');

        $q=trim((string)$request->query('q'));
        $status=trim((string)$request->query('status'));
        $priority=trim((string)$request->query('priority'));
        $type=trim((string)$request->query('type'));

        $query=DB::table('crm_followups as f')
            ->leftJoin('bookings as b','f.booking_id','=','b.id')
            ->leftJoin('admins as a','f.assigned_to','=','a.id')
            ->whereNull('f.deleted_at')
            ->select('f.*','b.prefix as booking_prefix','b.o_Name as booking_name','b.o_Email as booking_email','b.o_Phone as booking_phone','a.name as agent_name');

        if($status!=='') $query->where('f.status',$status);
        if($priority!=='') $query->where('f.priority',$priority);
        if($type!=='') $query->where('f.followup_type',$type);
        if($q!=='') {
            $like='%'.$q.'%';
            $query->where(function($w) use($like){
                $w->where('f.subject','like',$like)->orWhere('f.notes','like',$like)
                  ->orWhere('b.o_Name','like',$like)->orWhere('b.o_Email','like',$like)->orWhere('b.o_Phone','like',$like);
            });
        }

        $followups=$query->orderByRaw("CASE WHEN f.status='Completed' THEN 1 ELSE 0 END")->orderBy('f.due_at')->orderByDesc('f.id')->paginate(30)->withQueryString();

        $base=DB::table('crm_followups')->whereNull('deleted_at');
        $stats=[
            'today'=>(clone $base)->whereNotIn('status',['Completed','Cancelled'])->whereBetween('due_at',[now()->startOfDay(),now()->endOfDay()])->count(),
            'overdue'=>(clone $base)->whereNotIn('status',['Completed','Cancelled'])->where('due_at','<',now())->count(),
            'upcoming'=>(clone $base)->whereNotIn('status',['Completed','Cancelled'])->where('due_at','>',now()->endOfDay())->count(),
            'completed'=>(clone $base)->where('status','Completed')->count(),
            'unassigned'=>(clone $base)->whereNull('assigned_to')->whereNotIn('status',['Completed','Cancelled'])->count(),
        ];

        $admins=Schema::hasTable('admins')?DB::table('admins')->where('status','1')->orderBy('name')->get():collect();
        $freshBookings=Booking::latest()->limit(12)->get();
        $recentActivity=Schema::hasTable('booking_remarks')
            ? DB::table('booking_remarks as r')->leftJoin('bookings as b','r.booking_id','=','b.id')
              ->select('r.*','b.prefix','b.o_Name','b.o_Email')->orderByDesc('r.id')->limit(25)->get()
            : collect();

        return view('crm.followups-v2',compact('followups','stats','admins','freshBookings','recentActivity','q','status','priority','type'));
    }

    public function store(Request $request) {
        if ($r=$this->guard($request)) return $r;
        $d=$request->validate([
            'booking_id'=>'nullable|integer','subject'=>'required|string|max:255','followup_type'=>'required|string|max:50',
            'priority'=>'required|string|max:20','status'=>'required|string|max:30','assigned_to'=>'nullable|integer',
            'due_at'=>'required|date','notes'=>'nullable|string'
        ]);
        $id=DB::table('crm_followups')->insertGetId([
            'booking_id'=>$d['booking_id']?:null,'subject'=>$d['subject'],'followup_type'=>$d['followup_type'],
            'priority'=>$d['priority'],'status'=>$d['status'],'assigned_to'=>$d['assigned_to']?:null,
            'due_at'=>$d['due_at'],'notes'=>$d['notes']??null,'created_by'=>(int)$request->session()->get('crm_admin_id')?:null,
            'created_at'=>now(),'updated_at'=>now()
        ]);
        $this->audit($request,$id,'created',null,json_encode($d));
        return back()->with('crm_success','Follow-up created.');
    }

    public function update(Request $request,int $id) {
        if ($r=$this->guard($request)) return $r;
        $old=DB::table('crm_followups')->where('id',$id)->whereNull('deleted_at')->first(); abort_unless($old,404);
        $d=$request->validate([
            'subject'=>'required|string|max:255','followup_type'=>'required|string|max:50','priority'=>'required|string|max:20',
            'status'=>'required|string|max:30','assigned_to'=>'nullable|integer','due_at'=>'required|date','notes'=>'nullable|string'
        ]);
        DB::table('crm_followups')->where('id',$id)->update([
            'subject'=>$d['subject'],'followup_type'=>$d['followup_type'],'priority'=>$d['priority'],'status'=>$d['status'],
            'assigned_to'=>$d['assigned_to']?:null,'due_at'=>$d['due_at'],'notes'=>$d['notes']??null,'updated_at'=>now()
        ]);
        $this->audit($request,$id,'updated',json_encode($old),json_encode($d));
        return back()->with('crm_success','Follow-up updated.');
    }

    public function complete(Request $request,int $id) {
        if ($r=$this->guard($request)) return $r;
        DB::table('crm_followups')->where('id',$id)->whereNull('deleted_at')->update(['status'=>'Completed','completed_at'=>now(),'updated_at'=>now()]);
        $this->audit($request,$id,'completed',null,'Completed');
        return back()->with('crm_success','Follow-up completed.');
    }

    public function duplicate(Request $request,int $id) {
        if ($r=$this->guard($request)) return $r;
        $f=DB::table('crm_followups')->where('id',$id)->whereNull('deleted_at')->first(); abort_unless($f,404);
        $new=DB::table('crm_followups')->insertGetId([
            'booking_id'=>$f->booking_id,'subject'=>$f->subject.' (Copy)','followup_type'=>$f->followup_type,'priority'=>$f->priority,
            'status'=>'Pending','assigned_to'=>$f->assigned_to,'due_at'=>$f->due_at,'notes'=>$f->notes,
            'created_by'=>(int)$request->session()->get('crm_admin_id')?:null,'created_at'=>now(),'updated_at'=>now()
        ]);
        $this->audit($request,$new,'duplicated',(string)$id,(string)$new);
        return back()->with('crm_success','Follow-up duplicated.');
    }

    public function destroy(Request $request,int $id) {
        if ($r=$this->guard($request)) return $r;
        DB::table('crm_followups')->where('id',$id)->whereNull('deleted_at')->update(['deleted_at'=>now(),'updated_at'=>now()]);
        $this->audit($request,$id,'deleted',null,null);
        return back()->with('crm_success','Follow-up moved to trash.');
    }

    private function audit(Request $request,int $id,string $action,$old,$new): void {
        if(!Schema::hasTable('crm_followup_audit')) return;
        DB::table('crm_followup_audit')->insert([
            'followup_id'=>$id,'admin_id'=>(int)$request->session()->get('crm_admin_id')?:null,'action'=>$action,
            'old_value'=>$old,'new_value'=>$new,'created_at'=>now()
        ]);
    }
}
