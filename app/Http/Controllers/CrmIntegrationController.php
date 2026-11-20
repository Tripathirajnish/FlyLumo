<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
class CrmIntegrationController extends Controller {
 private function guard(Request $r){if(!$r->session()->has('crm_admin_id'))return redirect()->route('crm.login');abort_unless(Schema::hasTable('crm_integrations'),503,'Run php artisan migrate to enable Integration Hub.');return null;}
 private function cats(){return ['meta'=>'Meta Search','flight'=>'Flight API','hotel'=>'Hotel API','car'=>'Car Rental API','package'=>'Holiday / Package API','payment'=>'Payment','widget'=>'Chat / Widget','analytics'=>'Analytics / Tracking','ai'=>'AI Provider','other'=>'Other'];}
 public function index(Request $r){if($x=$this->guard($r))return $x;$items=DB::table('crm_integrations')->latest()->get();return view('crm.integrations', ['items'=>$items,'cats'=>$this->cats()]);}
 public function store(Request $r){if($x=$this->guard($r))return $x;$d=$r->validate(['name'=>'required|string|max:120','category'=>['required',Rule::in(array_keys($this->cats()))],'provider'=>'nullable|string|max:120','base_url'=>'nullable|string|max:500','auth_type'=>['required',Rule::in(['none','api_key','bearer','basic','token','oauth2','custom'])],'credential'=>'nullable|string|max:5000','status'=>['required',Rule::in(['draft','active','disabled'])],'field_map'=>'nullable|string|max:20000','settings'=>'nullable|string|max:20000']);foreach(['field_map','settings'] as $f){if(!empty($d[$f])){json_decode($d[$f],true);if(json_last_error()!==JSON_ERROR_NONE)return back()->withErrors([$f=>'Enter valid JSON for '.$f])->withInput();}}DB::table('crm_integrations')->insert(['name'=>$d['name'],'category'=>$d['category'],'provider'=>$d['provider']??null,'base_url'=>$d['base_url']??null,'auth_type'=>$d['auth_type'],'credentials_encrypted'=>!empty($d['credential'])?Crypt::encryptString($d['credential']):null,'status'=>$d['status'],'field_map'=>!empty($d['field_map'])?$d['field_map']:null,'settings'=>!empty($d['settings'])?$d['settings']:null,'created_by'=>(int)$r->session()->get('crm_admin_id'),'created_at'=>now(),'updated_at'=>now()]);return back()->with('crm_success','Integration configuration saved.');}
}
