<?php

namespace App\Http\Controllers;

use App\Services\SoapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApiControlController extends Controller
{
    private function guard(Request $request)
    {
        if (!$request->session()->has('crm_admin_id')) {
            return redirect()->route('crm.login');
        }
        return null;
    }

    /**
     * Strict check for actions that change the live supplier setup
     * (Demo/Live mode, endpoints, credentials).
     *
     * Enforced here in the controller so it applies on every route that
     * reaches these methods, including the legacy /crm/api-control-* routes
     * that the central permission gate does not map.
     *
     * Unlike CrmAccessService::can(), this does NOT fall back to "allow"
     * when RBAC is not installed or the admin has no role assigned.
     */
    private function requireApiManage(Request $request): void
    {
        $adminId = (int) $request->session()->get('crm_admin_id');
        $access  = app(\App\Services\CrmAccessService::class);

        $allowed = false;
        if ($adminId > 0 && $access->ready()) {
            $roleId = (int) DB::table('admins')->where('id', $adminId)->value('crm_access_role_id');
            $allowed = $roleId > 0 && $access->can($adminId, 'api_control.manage');
        }

        abort_unless($allowed, 403, 'You do not have permission to change API settings.');
    }

    public function index(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $mode = (string) DB::table('settings')->where('name','api_type')->value('value');
        if (!in_array($mode,['Demo','Live'],true)) $mode='Demo';

        $soap=app(SoapService::class);
        $config=[]; $configError=null;

        try {
            $search=$soap->getSettingAPiType('AeroSearch');
            $general=$soap->getSettingAPiType('AeroPrebook');
            $config=[
                'currency'=>$search['Currency']??null,
                'wsdl_configured'=>!empty($search['wsdl']),
                'wsdl_type'=>empty($search['wsdl'])?'Not configured':(filter_var($search['wsdl'],FILTER_VALIDATE_URL)?'Remote URL':'Local file'),
                'search_host'=>!empty($search['soap'])?(parse_url($search['soap'],PHP_URL_HOST)?:$search['soap']):null,
                'general_host'=>!empty($general['soap'])?(parse_url($general['soap'],PHP_URL_HOST)?:$general['soap']):null,
            ];
        } catch(\Throwable $e) {
            $configError=$e->getMessage();
        }

        $secure=$this->getCredentialStatus();

        $stats=[
            'airports'=>Schema::hasTable('airports')?DB::table('airports')->count():0,
            'airlines'=>Schema::hasTable('airlines')?DB::table('airlines')->count():0,
            'active_rules'=>Schema::hasTable('markups')?DB::table('markups')->whereIn('is_active',['Yes','1'])->count():0,
            'website_bookings'=>Schema::hasTable('bookings')?DB::table('bookings')->where('source','Website')->count():0,
            'fareleader_bookings'=>Schema::hasTable('bookings')?DB::table('bookings')->where('source','FareLeader')->count():0,
        ];

        $lastTest=Schema::hasTable('crm_api_control_logs')
            ? DB::table('crm_api_control_logs')->where('action','connection_test')->orderByDesc('id')->first()
            : null;

        $recentLogs=Schema::hasTable('crm_api_control_logs')
            ? DB::table('crm_api_control_logs as l')->leftJoin('admins as a','l.admin_id','=','a.id')
              ->select('l.*','a.name as admin_name')->orderByDesc('l.id')->limit(25)->get()
            : collect();

        return view('crm.api-control-v3-1',compact('mode','config','configError','secure','stats','lastTest','recentLogs'));
    }

    public function saveCredentials(Request $request)
    {
        if($redirect=$this->guard($request)) return $redirect;
        $this->requireApiManage($request);

        $data=$request->validate([
            'environment'=>['required','in:Demo,Live'],
            'api_login'=>['nullable','string','max:255'],
            'api_password'=>['nullable','string','max:255'],
            'device_id'=>['nullable','string','max:255'],
            'token'=>['nullable','string','max:255'],
            'currency'=>['required','string','max:10'],
            'wsdl'=>['nullable','string','max:2000'],
            'search_soap'=>['required','string','max:2000'],
            'general_soap'=>['required','string','max:2000'],
        ]);

        $env=strtolower($data['environment']);
        $map=[
            'api_login'=>"api_{$env}_login",
            'api_password'=>"api_{$env}_password",
            'device_id'=>"api_{$env}_device_id",
            'token'=>"api_{$env}_token",
            'currency'=>"api_{$env}_currency",
            'wsdl'=>"api_{$env}_wsdl",
            'search_soap'=>"api_{$env}_search_soap",
            'general_soap'=>"api_{$env}_general_soap",
        ];

        foreach($map as $input=>$name) {
            if(in_array($input,['api_login','api_password','device_id','token'],true)) {
                if(!filled($data[$input]??null)) continue;
                $value=Crypt::encryptString((string)$data[$input]);
            } else {
                $value=(string)($data[$input]??'');
            }

            DB::table('settings')->updateOrInsert(['name'=>$name],['value'=>$value,'updated_at'=>now()]);
        }

        $this->log($request,'credentials_update','success',$data['environment'].' credentials/config updated securely.',null);
        return back()->with('crm_success',$data['environment'].' API configuration saved.');
    }

    public function switchMode(Request $request)
    {
        if($redirect=$this->guard($request)) return $redirect;
        $this->requireApiManage($request);

        $data=$request->validate([
            'mode'=>['required','in:Demo,Live'],
            'confirm_live'=>['nullable','accepted_if:mode,Live'],
        ]);

        $old=(string)DB::table('settings')->where('name','api_type')->value('value');
        DB::table('settings')->updateOrInsert(['name'=>'api_type'],['value'=>$data['mode'],'updated_at'=>now()]);
        $this->log($request,'mode_switch','success',"{$old} -> {$data['mode']}",null);

        return back()->with('crm_success',"API mode switched to {$data['mode']}.");
    }

    public function testConnection(Request $request)
    {
        if($redirect=$this->guard($request)) return $redirect;

        $soap=app(SoapService::class);
        $started=microtime(true);

        try {
            $cfg=$soap->getSettingAPiType('AeroSearch');
            $wsdl=$cfg['wsdl']??'';
            $location=$cfg['soap']??'';

            if(!$wsdl) throw new \RuntimeException('WSDL is not configured for the current environment.');
            if(!$location) throw new \RuntimeException('Search SOAP endpoint is not configured.');

            new \SoapClient($wsdl,[
                'soap_version'=>SOAP_1_2,'trace'=>false,'exceptions'=>true,
                'cache_wsdl'=>WSDL_CACHE_NONE,'connection_timeout'=>15,
                'location'=>$location,'uri'=>'http://tempuri.org/',
            ]);

            $ms=(int)round((microtime(true)-$started)*1000);
            $this->log($request,'connection_test','success','WSDL loaded successfully. No supplier operation was called.',$ms);
            return back()->with('crm_success',"Connection test passed in {$ms} ms.");
        } catch(\Throwable $e) {
            $ms=(int)round((microtime(true)-$started)*1000);
            $msg=$this->safeError($e->getMessage());
            $this->log($request,'connection_test','failed',$msg,$ms);
            return back()->with('crm_error',"Connection test failed: {$msg}");
        }
    }

    private function getCredentialStatus(): array
    {
        $names=[];
        foreach(['demo','live'] as $env) {
            foreach(['login','password','device_id','token','currency','wsdl','search_soap','general_soap','soap'] as $suffix) {
                $names[]="api_{$env}_{$suffix}";
            }
        }
        $rows=DB::table('settings')->whereIn('name',$names)->pluck('value','name');

        $out=[];
        foreach(['demo','live'] as $env) {
            $legacy=$rows["api_{$env}_soap"]??'';
            $out[$env]=[
                'login'=>filled($rows["api_{$env}_login"]??null),
                'password'=>filled($rows["api_{$env}_password"]??null),
                'device_id'=>filled($rows["api_{$env}_device_id"]??null),
                'token'=>filled($rows["api_{$env}_token"]??null),
                'currency'=>$rows["api_{$env}_currency"]??($env==='live'?'USD':'EUR'),
                'wsdl'=>$rows["api_{$env}_wsdl"]??'',
                'search_soap'=>$rows["api_{$env}_search_soap"]??$legacy,
                'general_soap'=>$rows["api_{$env}_general_soap"]??$legacy,
            ];
        }
        return $out;
    }

    private function log(Request $request,string $action,string $status,?string $message,?int $latencyMs): void
    {
        if(!Schema::hasTable('crm_api_control_logs')) return;
        $mode=(string)DB::table('settings')->where('name','api_type')->value('value');

        DB::table('crm_api_control_logs')->insert([
            'admin_id'=>(int)$request->session()->get('crm_admin_id')?:null,
            'mode'=>$mode?:null,'action'=>$action,'status'=>$status,
            'message'=>$message,'latency_ms'=>$latencyMs,'created_at'=>now(),
        ]);
    }

    private function safeError(string $message): string
    {
        $message=preg_replace('/(ApiPassword|Password|TokenGuid|token)\s*[:=]\s*[^\s,;]+/i','$1=[REDACTED]',$message);
        return mb_substr($message,0,1000);
    }
}