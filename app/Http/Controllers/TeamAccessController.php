<?php

namespace App\Http\Controllers;

use App\Services\CrmAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class TeamAccessController extends Controller
{
    public function __construct(private CrmAccessService $access) {}

    private function guard(Request $request)
    {
        return $request->session()->has('crm_admin_id') ? null : redirect()->route('crm.login');
    }

    public function index(Request $request)
    {
        if ($r=$this->guard($request)) return $r;

        $admins = DB::table('admins as a')
            ->leftJoin('crm_access_roles as r','a.crm_access_role_id','=','r.id')
            ->whereNull('a.crm_deleted_at')
            ->select('a.*','r.name as access_role_name','r.slug as access_role_slug')
            ->orderBy('a.name')
            ->get();

        $roles = DB::table('crm_access_roles')
            ->where('is_active',1)
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        $rolePermissions = DB::table('crm_access_role_permissions')
            ->get()
            ->groupBy('role_id')
            ->map(fn($rows) => $rows->pluck('permission_key')->values()->all());

        $overrides = DB::table('crm_admin_permission_overrides')
            ->get()
            ->groupBy('admin_id')
            ->map(fn($rows) => $rows->pluck('allowed','permission_key')->map(fn($v)=>(int)$v)->all());

        $permissionGroups = CrmAccessService::groups();
        $currentAdminId = (int)$request->session()->get('crm_admin_id');

        $stats = [
            'users' => $admins->count(),
            'active' => $admins->where('status','1')->count(),
            'roles' => $roles->count(),
            'customized' => $overrides->count(),
        ];

        return view('crm.team-access',compact(
            'admins','roles','rolePermissions','overrides','permissionGroups',
            'currentAdminId','stats'
        ));
    }

    public function storeUser(Request $request)
    {
        if ($r=$this->guard($request)) return $r;

        $data=$request->validate([
            'name'=>['required','string','max:150'],
            'username'=>['required','string','max:120',Rule::unique('admins','username')],
            'emailid'=>['nullable','email','max:190'],
            'mobile'=>['nullable','string','max:50'],
            'password'=>['required','string','min:8','confirmed'],
            'crm_access_role_id'=>['required','integer',Rule::exists('crm_access_roles','id')->where(fn($q)=>$q->where('is_active',1))],
            'status'=>['required','in:0,1'],
        ]);

        /*
         * `admins.role_id` is a legacy NOT NULL column still used by older CRM code.
         * New CRM permissions are controlled by `crm_access_role_id`.
         *
         * For backward compatibility, inherit the signed-in admin's legacy role_id
         * instead of inserting NULL. This avoids changing the database schema and
         * does not replace the new CRM access-role permission system.
         */
        $legacyRoleId = DB::table('admins')
            ->where('id', (int)$request->session()->get('crm_admin_id'))
            ->value('role_id');

        if ($legacyRoleId === null) {
            return back()
                ->withInput()
                ->withErrors([
                    'crm_access_role_id' => 'Unable to determine the legacy CRM role. Please verify the signed-in admin account.'
                ]);
        }

        $id=DB::table('admins')->insertGetId([
            'name'=>$data['name'],
            'username'=>$data['username'],
            'emailid'=>$data['emailid']??null,
            'mobile'=>$data['mobile']??null,
            'password'=>Hash::make($data['password']),
            'role_id'=>(int)$legacyRoleId,
            'crm_access_role_id'=>$data['crm_access_role_id'],
            'status'=>$data['status'],
            'crm_deleted_at'=>null,
            'created_at'=>now(),
            'updated_at'=>now(),
        ]);

        $this->audit($request,'user_created',$id,'Created CRM user '.$data['username']);
        return back()->with('crm_success','CRM user created successfully.');
    }

    public function updateUser(Request $request, int $id)
    {
        if ($r=$this->guard($request)) return $r;

        $user=DB::table('admins')->where('id',$id)->whereNull('crm_deleted_at')->first();
        abort_unless($user,404);

        $data=$request->validate([
            'name'=>['required','string','max:150'],
            'username'=>['required','string','max:120',Rule::unique('admins','username')->ignore($id)],
            'emailid'=>['nullable','email','max:190'],
            'mobile'=>['nullable','string','max:50'],
            'password'=>['nullable','string','min:8','confirmed'],
            'crm_access_role_id'=>['required','integer',Rule::exists('crm_access_roles','id')->where(fn($q)=>$q->where('is_active',1))],
            'status'=>['required','in:0,1'],
        ]);

        $this->protectLastSuperAdmin($id,(int)$data['crm_access_role_id'],(string)$data['status']);

        if ((int)$request->session()->get('crm_admin_id') === $id && $data['status'] !== '1') {
            return back()->withErrors(['status'=>'You cannot deactivate your own signed-in account.']);
        }

        $update=[
            'name'=>$data['name'],
            'username'=>$data['username'],
            'emailid'=>$data['emailid']??null,
            'mobile'=>$data['mobile']??null,
            'crm_access_role_id'=>$data['crm_access_role_id'],
            'status'=>$data['status'],
            'updated_at'=>now(),
        ];
        if(!empty($data['password'])) $update['password']=Hash::make($data['password']);

        DB::table('admins')->where('id',$id)->update($update);
        $this->audit($request,'user_updated',$id,'Updated CRM user '.$data['username']);
        return back()->with('crm_success','CRM user updated.');
    }

    public function toggleUser(Request $request, int $id)
    {
        if ($r=$this->guard($request)) return $r;

        $user=DB::table('admins')->where('id',$id)->whereNull('crm_deleted_at')->first();
        abort_unless($user,404);

        if ((int)$request->session()->get('crm_admin_id') === $id) {
            return back()->withErrors(['user'=>'You cannot deactivate your own signed-in account.']);
        }

        $new=(string)$user->status === '1' ? '0' : '1';
        $this->protectLastSuperAdmin($id,(int)$user->crm_access_role_id,$new);

        DB::table('admins')->where('id',$id)->update(['status'=>$new,'updated_at'=>now()]);
        $this->audit($request,'user_status',$id,'Changed user status to '.($new==='1'?'Active':'Inactive'));

        return back()->with('crm_success','User status updated.');
    }

    public function deleteUser(Request $request, int $id)
    {
        if ($r=$this->guard($request)) return $r;

        if ((int)$request->session()->get('crm_admin_id') === $id) {
            return back()->withErrors(['user'=>'You cannot delete your own signed-in account.']);
        }

        $user=DB::table('admins')->where('id',$id)->whereNull('crm_deleted_at')->first();
        abort_unless($user,404);

        $this->protectLastSuperAdmin($id,(int)$user->crm_access_role_id,'0');

        DB::table('admins')->where('id',$id)->update([
            'status'=>'0',
            'crm_deleted_at'=>now(),
            'updated_at'=>now(),
        ]);
        DB::table('crm_admin_permission_overrides')->where('admin_id',$id)->delete();

        $this->audit($request,'user_deleted',$id,'Soft-deleted CRM user '.$user->username);
        return back()->with('crm_success','User removed from CRM access. Historical activity is preserved.');
    }

    public function saveUserPermissions(Request $request, int $id)
    {
        if ($r=$this->guard($request)) return $r;

        $user=DB::table('admins')->where('id',$id)->whereNull('crm_deleted_at')->first();
        abort_unless($user,404);

        $requested=array_values(array_intersect(
            array_keys(CrmAccessService::PERMISSIONS),
            $request->input('permissions',[])
        ));

        $rolePerms=$this->access->rolePermissions((int)$user->crm_access_role_id);
        $roleSet=array_fill_keys($rolePerms,true);
        $requestedSet=array_fill_keys($requested,true);

        DB::transaction(function() use($id,$roleSet,$requestedSet){
            DB::table('crm_admin_permission_overrides')->where('admin_id',$id)->delete();

            foreach(array_keys(CrmAccessService::PERMISSIONS) as $key){
                $baseline=isset($roleSet[$key]);
                $wanted=isset($requestedSet[$key]);
                if($baseline===$wanted) continue;

                DB::table('crm_admin_permission_overrides')->insert([
                    'admin_id'=>$id,
                    'permission_key'=>$key,
                    'allowed'=>$wanted?1:0,
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
            }
        });

        $this->audit($request,'user_permissions',$id,'Updated custom user permission overrides');
        return back()->with('crm_success','Custom permissions saved.');
    }

    public function resetUserPermissions(Request $request, int $id)
    {
        if ($r=$this->guard($request)) return $r;
        DB::table('crm_admin_permission_overrides')->where('admin_id',$id)->delete();
        $this->audit($request,'user_permissions_reset',$id,'Reset user permissions to role defaults');
        return back()->with('crm_success','User permissions reset to role defaults.');
    }

    public function storeRole(Request $request)
    {
        if ($r=$this->guard($request)) return $r;

        $data=$request->validate([
            'name'=>['required','string','max:100',Rule::unique('crm_access_roles','name')],
            'permissions'=>['array'],
            'permissions.*'=>['string'],
        ]);

        $slug=$this->uniqueSlug($data['name']);
        $roleId=DB::table('crm_access_roles')->insertGetId([
            'name'=>$data['name'],'slug'=>$slug,'description'=>'Custom CRM role',
            'is_system'=>0,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()
        ]);

        $this->replaceRolePermissions($roleId,$data['permissions']??[]);
        $this->audit($request,'role_created',null,'Created role '.$data['name']);
        return back()->with('crm_success','Role created.');
    }

    public function updateRole(Request $request, int $id)
    {
        if ($r=$this->guard($request)) return $r;

        $role=DB::table('crm_access_roles')->where('id',$id)->first();
        abort_unless($role,404);

        $data=$request->validate([
            'name'=>['required','string','max:100',Rule::unique('crm_access_roles','name')->ignore($id)],
            'permissions'=>['array'],
            'permissions.*'=>['string'],
        ]);

        if($role->slug==='super-admin'){
            DB::table('crm_access_roles')->where('id',$id)->update(['name'=>$data['name'],'updated_at'=>now()]);
            $this->replaceRolePermissions($id,array_keys(CrmAccessService::PERMISSIONS));
        } else {
            DB::table('crm_access_roles')->where('id',$id)->update(['name'=>$data['name'],'updated_at'=>now()]);
            $this->replaceRolePermissions($id,$data['permissions']??[]);
        }

        $this->audit($request,'role_updated',null,'Updated role '.$data['name']);
        return back()->with('crm_success','Role permissions updated.');
    }

    public function deleteRole(Request $request, int $id)
    {
        if ($r=$this->guard($request)) return $r;

        $role=DB::table('crm_access_roles')->where('id',$id)->first();
        abort_unless($role,404);

        if((int)$role->is_system){
            return back()->withErrors(['role'=>'System roles cannot be deleted.']);
        }

        if(DB::table('admins')->whereNull('crm_deleted_at')->where('crm_access_role_id',$id)->exists()){
            return back()->withErrors(['role'=>'This role is assigned to one or more users. Reassign those users first.']);
        }

        DB::table('crm_access_role_permissions')->where('role_id',$id)->delete();
        DB::table('crm_access_roles')->where('id',$id)->delete();
        $this->audit($request,'role_deleted',null,'Deleted role '.$role->name);

        return back()->with('crm_success','Role deleted.');
    }

    private function replaceRolePermissions(int $roleId, array $permissions): void
    {
        $allowed=array_values(array_intersect(array_keys(CrmAccessService::PERMISSIONS),$permissions));
        DB::transaction(function() use($roleId,$allowed){
            DB::table('crm_access_role_permissions')->where('role_id',$roleId)->delete();
            foreach($allowed as $p){
                DB::table('crm_access_role_permissions')->insert([
                    'role_id'=>$roleId,'permission_key'=>$p,'created_at'=>now(),'updated_at'=>now()
                ]);
            }
        });
    }

    private function protectLastSuperAdmin(int $targetId, int $newRoleId, string $newStatus): void
    {
        $superId=(int)DB::table('crm_access_roles')->where('slug','super-admin')->value('id');
        $target=DB::table('admins')->where('id',$targetId)->first();
        if(!$target || (int)$target->crm_access_role_id!==$superId || (string)$target->status!=='1') return;

        $willRemainSuper=(int)$newRoleId===$superId && $newStatus==='1';
        if($willRemainSuper) return;

        $count=DB::table('admins')
            ->whereNull('crm_deleted_at')
            ->where('status','1')
            ->where('crm_access_role_id',$superId)
            ->count();

        if($count<=1){
            abort(422,'At least one active Super Admin must remain.');
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base=\Illuminate\Support\Str::slug($name) ?: 'role';
        $slug=$base; $i=2;
        while(DB::table('crm_access_roles')->where('slug',$slug)->exists()){
            $slug=$base.'-'.$i++;
        }
        return $slug;
    }

    private function audit(Request $request,string $action,?int $targetAdminId,string $message): void
    {
        if(!Schema::hasTable('crm_access_audit')) return;
        DB::table('crm_access_audit')->insert([
            'actor_admin_id'=>(int)$request->session()->get('crm_admin_id')?:null,
            'target_admin_id'=>$targetAdminId,
            'action'=>$action,
            'message'=>$message,
            'ip_address'=>$request->ip(),
            'created_at'=>now(),
        ]);
    }
}