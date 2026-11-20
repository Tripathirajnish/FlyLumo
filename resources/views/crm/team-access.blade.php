@extends('crm.layout')
@section('content')
@php
    $allPermissionKeys=array_keys(\App\Services\CrmAccessService::PERMISSIONS);
@endphp

<div class="rbac-page">
    <div class="rbac-hero">
        <div>
            <span class="rbac-kicker"><i class="fa-solid fa-shield-halved"></i> SECURITY & ACCESS</span>
            <h1>Team & Access</h1>
            <p>Create CRM users, manage roles and control exactly what each person can view or change.</p>
        </div>
        <div class="rbac-hero-actions">
            <a href="{{ route('crm.settings') }}" class="btn btn-light">Back to Settings</a>
            <button class="btn btn-primary" type="button" onclick="rbacOpen('rbacNewUser')"><i class="fa-solid fa-user-plus me-1"></i>Add User</button>
        </div>
    </div>

    @if(session('crm_error'))<div class="alert alert-danger">{{session('crm_error')}}</div>@endif

    <div class="rbac-stats">
        <div class="rbac-stat"><span>CRM Users</span><strong>{{$stats['users']}}</strong><small>Total non-deleted accounts</small></div>
        <div class="rbac-stat"><span>Active Users</span><strong>{{$stats['active']}}</strong><small>Can currently sign in</small></div>
        <div class="rbac-stat"><span>Roles</span><strong>{{$stats['roles']}}</strong><small>Access profiles</small></div>
        <div class="rbac-stat"><span>Customized</span><strong>{{$stats['customized']}}</strong><small>Users with overrides</small></div>
    </div>

    <div class="rbac-tabs">
        <button class="active" type="button" data-rbac-tab="users">Users</button>
        <button type="button" data-rbac-tab="roles">Roles & Permissions</button>
    </div>

    <section class="rbac-tab-panel active" data-rbac-panel="users">
        <div class="rbac-toolbar">
            <div>
                <h2>CRM Users</h2>
                <p>Existing users were preserved as Super Admin during installation so nobody is accidentally locked out.</p>
            </div>
            <div class="rbac-search"><i class="fa-solid fa-magnifying-glass"></i><input id="rbacUserSearch" placeholder="Search name, username or email"></div>
        </div>

        <div class="rbac-user-grid" id="rbacUserGrid">
        @foreach($admins as $a)
            @php
                $rolePerms=$rolePermissions[$a->crm_access_role_id]??[];
                $userOverrides=$overrides[$a->id]??[];
                $effective=[];
                foreach($allPermissionKeys as $pk){
                    $baseline=in_array($pk,$rolePerms,true) || $a->access_role_slug==='super-admin';
                    $effective[$pk]=array_key_exists($pk,$userOverrides)?(bool)$userOverrides[$pk]:$baseline;
                }
            @endphp
            <article class="rbac-user-card" data-search="{{ strtolower(($a->name??'').' '.($a->username??'').' '.($a->emailid??'')) }}">
                <div class="rbac-user-head">
                    <div class="rbac-avatar">{{ strtoupper(substr($a->name?:'U',0,2)) }}</div>
                    <div class="rbac-user-name">
                        <h3>{{$a->name}}</h3>
                        <span>@ {{$a->username}}</span>
                    </div>
                    <span class="rbac-status {{$a->status==='1'?'active':'inactive'}}">{{$a->status==='1'?'Active':'Inactive'}}</span>
                </div>
                <div class="rbac-user-meta">
                    <div><span>Role</span><strong>{{$a->access_role_name?:'Unassigned'}}</strong></div>
                    <div><span>Email</span><strong>{{$a->emailid?:'—'}}</strong></div>
                    <div><span>Mobile</span><strong>{{$a->mobile?:'—'}}</strong></div>
                    <div><span>Custom overrides</span><strong>{{count($userOverrides)}}</strong></div>
                </div>
                <div class="rbac-user-actions">
                    <button class="btn btn-light btn-sm" type="button" onclick="rbacOpen('rbacEdit{{$a->id}}')"><i class="fa-solid fa-pen"></i>Edit</button>
                    <button class="btn btn-light btn-sm" type="button" onclick="rbacOpen('rbacPerm{{$a->id}}')"><i class="fa-solid fa-key"></i>Permissions</button>

                    @if((int)$a->id !== $currentAdminId)
                    <form method="POST" action="{{route('crm.user_toggle',$a->id)}}">@csrf
                        <button class="btn btn-light btn-sm">{{$a->status==='1'?'Deactivate':'Activate'}}</button>
                    </form>
                    <form method="POST" action="{{route('crm.user_delete',$a->id)}}" onsubmit="return confirm('Remove this user from CRM access? Historical activity will be preserved.');">@csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm">Delete</button>
                    </form>
                    @else
                        <span class="rbac-self">Signed in</span>
                    @endif
                </div>
            </article>

            <div class="rbac-modal" id="rbacEdit{{$a->id}}">
                <div class="rbac-dialog">
                    <form method="POST" action="{{route('crm.user_update',$a->id)}}">@csrf
                        <div class="rbac-modal-head"><div><span>Edit user</span><h3>{{$a->name}}</h3></div><button type="button" onclick="rbacClose('rbacEdit{{$a->id}}')">×</button></div>
                        <div class="rbac-modal-body">
                            <div class="rbac-form-grid">
                                <label><span>Name</span><input class="form-control" name="name" value="{{$a->name}}" required></label>
                                <label><span>Username</span><input class="form-control" name="username" value="{{$a->username}}" required></label>
                                <label><span>Email</span><input class="form-control" type="email" name="emailid" value="{{$a->emailid}}"></label>
                                <label><span>Mobile</span><input class="form-control" name="mobile" value="{{$a->mobile}}"></label>
                                <label><span>Access Role</span><select class="form-select" name="crm_access_role_id" required>@foreach($roles as $r)<option value="{{$r->id}}" @selected((int)$a->crm_access_role_id===(int)$r->id)>{{$r->name}}</option>@endforeach</select></label>
                                <label><span>Status</span><select class="form-select" name="status" required><option value="1" @selected($a->status==='1')>Active</option><option value="0" @selected($a->status!=='1')>Inactive</option></select></label>
                                <label><span>New Password (optional)</span><input class="form-control" type="password" name="password" autocomplete="new-password"></label>
                                <label><span>Confirm New Password</span><input class="form-control" type="password" name="password_confirmation" autocomplete="new-password"></label>
                            </div>
                        </div>
                        <div class="rbac-modal-actions"><button type="button" class="btn btn-light" onclick="rbacClose('rbacEdit{{$a->id}}')">Cancel</button><button class="btn btn-primary">Save User</button></div>
                    </form>
                </div>
            </div>

            <div class="rbac-modal" id="rbacPerm{{$a->id}}">
                <div class="rbac-dialog wide">
                    <form method="POST" action="{{route('crm.user_permissions',$a->id)}}">@csrf
                        <div class="rbac-modal-head"><div><span>Custom permissions</span><h3>{{$a->name}}</h3><small>Unchecked/checked differences from the role become user-specific overrides.</small></div><button type="button" onclick="rbacClose('rbacPerm{{$a->id}}')">×</button></div>
                        <div class="rbac-modal-body">
                            <div class="rbac-perm-note">Role baseline: <b>{{$a->access_role_name}}</b>. Use this only when one user needs access different from their role.</div>
                            @foreach($permissionGroups as $group=>$permissions)
                            <div class="rbac-perm-group">
                                <div class="rbac-perm-group-head"><strong>{{$group}}</strong><button type="button" class="rbac-check-all">Select all</button></div>
                                <div class="rbac-perm-grid">
                                @foreach($permissions as $key=>$desc)
                                    <label class="rbac-perm-item">
                                        <input type="checkbox" name="permissions[]" value="{{$key}}" @checked($effective[$key]??false)>
                                        <span><b>{{str_replace(['.','_'],' ',ucwords($key,'.'))}}</b><small>{{$desc}}</small></span>
                                    </label>
                                @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="rbac-modal-actions">
                            <button form="rbacReset{{$a->id}}" type="submit" class="btn btn-light">Reset to Role Defaults</button>
                            <button type="button" class="btn btn-light" onclick="rbacClose('rbacPerm{{$a->id}}')">Cancel</button>
                            <button class="btn btn-primary">Save Custom Permissions</button>
                        </div>
                    </form>
                    <form id="rbacReset{{$a->id}}" method="POST" action="{{route('crm.user_permissions_reset',$a->id)}}">@csrf</form>
                </div>
            </div>
        @endforeach
        </div>
    </section>

    <section class="rbac-tab-panel" data-rbac-panel="roles">
        <div class="rbac-toolbar">
            <div><h2>Roles & Permission Profiles</h2><p>Create reusable access profiles. Super Admin is permanently protected with full access.</p></div>
            <button class="btn btn-primary" type="button" onclick="rbacOpen('rbacNewRole')"><i class="fa-solid fa-plus me-1"></i>Create Role</button>
        </div>

        <div class="rbac-role-grid">
        @foreach($roles as $role)
            @php $rp=$role->slug==='super-admin'?$allPermissionKeys:($rolePermissions[$role->id]??[]); @endphp
            <article class="rbac-role-card">
                <div class="rbac-role-head">
                    <div><span>{{$role->is_system?'SYSTEM ROLE':'CUSTOM ROLE'}}</span><h3>{{$role->name}}</h3><p>{{$role->description}}</p></div>
                    <strong>{{count($rp)}}/{{count($allPermissionKeys)}}</strong>
                </div>
                <div class="rbac-progress"><span style="width:{{count($allPermissionKeys)?round(count($rp)/count($allPermissionKeys)*100):0}}%"></span></div>
                <div class="rbac-role-actions">
                    <button class="btn btn-light btn-sm" type="button" onclick="rbacOpen('rbacRole{{$role->id}}')">Edit Permissions</button>
                    @if(!$role->is_system)
                    <form method="POST" action="{{route('crm.role_delete',$role->id)}}" onsubmit="return confirm('Delete this role?');">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm">Delete</button></form>
                    @endif
                </div>
            </article>

            <div class="rbac-modal" id="rbacRole{{$role->id}}">
                <div class="rbac-dialog wide">
                    <form method="POST" action="{{route('crm.role_update',$role->id)}}">@csrf
                        <div class="rbac-modal-head"><div><span>Role permissions</span><h3>{{$role->name}}</h3></div><button type="button" onclick="rbacClose('rbacRole{{$role->id}}')">×</button></div>
                        <div class="rbac-modal-body">
                            <label class="rbac-role-name"><span>Role Name</span><input class="form-control" name="name" value="{{$role->name}}" required></label>
                            @if($role->slug==='super-admin')<div class="alert alert-info">Super Admin always retains every permission. This protects the CRM from administrative lockout.</div>@endif
                            @foreach($permissionGroups as $group=>$permissions)
                            <div class="rbac-perm-group">
                                <div class="rbac-perm-group-head"><strong>{{$group}}</strong><button type="button" class="rbac-check-all">Select all</button></div>
                                <div class="rbac-perm-grid">
                                @foreach($permissions as $key=>$desc)
                                <label class="rbac-perm-item {{$role->slug==='super-admin'?'locked':''}}">
                                    <input type="checkbox" name="permissions[]" value="{{$key}}" @checked(in_array($key,$rp,true)) {{$role->slug==='super-admin'?'disabled':''}}>
                                    @if($role->slug==='super-admin')<input type="hidden" name="permissions[]" value="{{$key}}">@endif
                                    <span><b>{{str_replace(['.','_'],' ',ucwords($key,'.'))}}</b><small>{{$desc}}</small></span>
                                </label>
                                @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="rbac-modal-actions"><button type="button" class="btn btn-light" onclick="rbacClose('rbacRole{{$role->id}}')">Cancel</button><button class="btn btn-primary">Save Role</button></div>
                    </form>
                </div>
            </div>
        @endforeach
        </div>
    </section>
</div>

<div class="rbac-modal" id="rbacNewUser">
    <div class="rbac-dialog">
        <form method="POST" action="{{route('crm.user_store')}}">@csrf
            <div class="rbac-modal-head"><div><span>New CRM user</span><h3>Create User</h3></div><button type="button" onclick="rbacClose('rbacNewUser')">×</button></div>
            <div class="rbac-modal-body"><div class="rbac-form-grid">
                <label><span>Name</span><input class="form-control" name="name" required></label>
                <label><span>Username</span><input class="form-control" name="username" required></label>
                <label><span>Email</span><input class="form-control" type="email" name="emailid"></label>
                <label><span>Mobile</span><input class="form-control" name="mobile"></label>
                <label><span>Password</span><input class="form-control" type="password" name="password" required autocomplete="new-password"></label>
                <label><span>Confirm Password</span><input class="form-control" type="password" name="password_confirmation" required autocomplete="new-password"></label>
                <label><span>Access Role</span><select class="form-select" name="crm_access_role_id" required>@foreach($roles as $r)<option value="{{$r->id}}">{{$r->name}}</option>@endforeach</select></label>
                <label><span>Status</span><select class="form-select" name="status"><option value="1">Active</option><option value="0">Inactive</option></select></label>
            </div></div>
            <div class="rbac-modal-actions"><button type="button" class="btn btn-light" onclick="rbacClose('rbacNewUser')">Cancel</button><button class="btn btn-primary">Create User</button></div>
        </form>
    </div>
</div>

<div class="rbac-modal" id="rbacNewRole">
    <div class="rbac-dialog wide">
        <form method="POST" action="{{route('crm.role_store')}}">@csrf
            <div class="rbac-modal-head"><div><span>New permission profile</span><h3>Create Role</h3></div><button type="button" onclick="rbacClose('rbacNewRole')">×</button></div>
            <div class="rbac-modal-body">
                <label class="rbac-role-name"><span>Role Name</span><input class="form-control" name="name" placeholder="e.g. Senior Agent" required></label>
                @foreach($permissionGroups as $group=>$permissions)
                <div class="rbac-perm-group">
                    <div class="rbac-perm-group-head"><strong>{{$group}}</strong><button type="button" class="rbac-check-all">Select all</button></div>
                    <div class="rbac-perm-grid">
                    @foreach($permissions as $key=>$desc)
                        <label class="rbac-perm-item"><input type="checkbox" name="permissions[]" value="{{$key}}"><span><b>{{str_replace(['.','_'],' ',ucwords($key,'.'))}}</b><small>{{$desc}}</small></span></label>
                    @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            <div class="rbac-modal-actions"><button type="button" class="btn btn-light" onclick="rbacClose('rbacNewRole')">Cancel</button><button class="btn btn-primary">Create Role</button></div>
        </form>
    </div>
</div>

<script>
function rbacOpen(id){document.getElementById(id)?.classList.add('show');document.body.style.overflow='hidden'}
function rbacClose(id){document.getElementById(id)?.classList.remove('show');document.body.style.overflow=''}
document.querySelectorAll('.rbac-modal').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)rbacClose(m.id)}));
document.querySelectorAll('[data-rbac-tab]').forEach(b=>b.addEventListener('click',()=>{
    document.querySelectorAll('[data-rbac-tab]').forEach(x=>x.classList.remove('active'));
    document.querySelectorAll('[data-rbac-panel]').forEach(x=>x.classList.remove('active'));
    b.classList.add('active');document.querySelector('[data-rbac-panel="'+b.dataset.rbacTab+'"]').classList.add('active');
}));
document.getElementById('rbacUserSearch')?.addEventListener('input',function(){
    const q=this.value.toLowerCase().trim();
    document.querySelectorAll('.rbac-user-card').forEach(c=>c.style.display=!q||c.dataset.search.includes(q)?'':'none');
});
document.querySelectorAll('.rbac-check-all').forEach(btn=>btn.addEventListener('click',function(){
    const group=btn.closest('.rbac-perm-group');
    const boxes=[...group.querySelectorAll('input[type=checkbox]:not(:disabled)')];
    const shouldCheck=boxes.some(b=>!b.checked);boxes.forEach(b=>b.checked=shouldCheck);
    btn.textContent=shouldCheck?'Clear all':'Select all';
}));
</script>
@endsection
