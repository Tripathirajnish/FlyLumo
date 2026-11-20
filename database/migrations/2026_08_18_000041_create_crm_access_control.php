<?php

use App\Services\CrmAccessService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if(!Schema::hasTable('crm_access_roles')){
            Schema::create('crm_access_roles',function(Blueprint $t){
                $t->id();
                $t->string('name',100)->unique();
                $t->string('slug',120)->unique();
                $t->string('description')->nullable();
                $t->boolean('is_system')->default(false);
                $t->boolean('is_active')->default(true);
                $t->timestamps();
            });
        }

        if(!Schema::hasTable('crm_access_role_permissions')){
            Schema::create('crm_access_role_permissions',function(Blueprint $t){
                $t->id();
                $t->unsignedBigInteger('role_id')->index();
                $t->string('permission_key',100)->index();
                $t->timestamps();
                $t->unique(['role_id','permission_key']);
            });
        }

        if(!Schema::hasTable('crm_admin_permission_overrides')){
            Schema::create('crm_admin_permission_overrides',function(Blueprint $t){
                $t->id();
                $t->unsignedBigInteger('admin_id')->index();
                $t->string('permission_key',100)->index();
                $t->boolean('allowed');
                $t->timestamps();
                $t->unique(['admin_id','permission_key']);
            });
        }

        if(!Schema::hasTable('crm_access_audit')){
            Schema::create('crm_access_audit',function(Blueprint $t){
                $t->id();
                $t->unsignedBigInteger('actor_admin_id')->nullable()->index();
                $t->unsignedBigInteger('target_admin_id')->nullable()->index();
                $t->string('action',80)->index();
                $t->text('message')->nullable();
                $t->string('ip_address',64)->nullable();
                $t->timestamp('created_at')->nullable()->index();
            });
        }

        if(Schema::hasTable('admins')){
            Schema::table('admins',function(Blueprint $t){
                if(!Schema::hasColumn('admins','crm_access_role_id')) $t->unsignedBigInteger('crm_access_role_id')->nullable()->index();
                if(!Schema::hasColumn('admins','crm_deleted_at')) $t->timestamp('crm_deleted_at')->nullable()->index();
            });
        }

        $roles=[
            'super-admin'=>['Super Admin','Full CRM access',array_keys(CrmAccessService::PERMISSIONS)],
            'manager'=>['Manager','Operational manager',[
                'dashboard.view','bookings.view','bookings.edit','passengers.manage','itinerary.manage','fares.manage',
                'customers.view','customers.edit','leads.view','leads.edit','payments.view','payments.edit',
                'communications.view','communications.send','followups.view','followups.manage',
                'authorizations.view','authorizations.manage','fare_rules.view','fare_rules.manage',
                'reports.view','reports.export','notifications.view','email_templates.view','email_templates.manage',
                'tracking.view','tracking.add_note','website_analytics.view','cms.view','settings.view',
                'trash.view','trash.manage','records.manage'
            ]],
            'agent'=>['Agent','Booking and customer servicing agent',[
                'dashboard.view','bookings.view','bookings.edit','passengers.manage','itinerary.manage',
                'customers.view','customers.edit','leads.view','leads.edit',
                'communications.view','communications.send','followups.view','followups.manage',
                'authorizations.view','authorizations.manage','payments.view','notifications.view',
                'tracking.view','tracking.add_note','records.manage'
            ]],
            'finance'=>['Finance','Payment, fare and authorization operations',[
                'dashboard.view','bookings.view','fares.manage','payments.view','payments.edit',
                'authorizations.view','authorizations.manage','reports.view','reports.export',
                'tracking.view','notifications.view'
            ]],
            'content-manager'=>['Content Manager','Website content and email template management',[
                'dashboard.view','cms.view','cms.manage','email_templates.view','email_templates.manage',
                'website_analytics.view','settings.view'
            ]],
            'read-only'=>['Read Only / Auditor','Read-only CRM visibility',[
                'dashboard.view','bookings.view','customers.view','leads.view','payments.view',
                'communications.view','followups.view','authorizations.view','fare_rules.view',
                'reports.view','notifications.view','email_templates.view','api_control.view',
                'tracking.view','website_analytics.view','cms.view','settings.view','users.view','trash.view'
            ]],
        ];

        foreach($roles as $slug=>[$name,$description,$permissions]){
            $roleId=DB::table('crm_access_roles')->updateOrInsert(
                ['slug'=>$slug],
                ['name'=>$name,'description'=>$description,'is_system'=>1,'is_active'=>1,'updated_at'=>now(),'created_at'=>now()]
            );
            $id=(int)DB::table('crm_access_roles')->where('slug',$slug)->value('id');

            DB::table('crm_access_role_permissions')->where('role_id',$id)->delete();
            foreach($permissions as $p){
                DB::table('crm_access_role_permissions')->insert([
                    'role_id'=>$id,'permission_key'=>$p,'created_at'=>now(),'updated_at'=>now()
                ]);
            }
        }

        // Preserve current behaviour: every existing admin starts as Super Admin.
        // The owner can then safely downgrade individual users from Team & Access.
        $superId=(int)DB::table('crm_access_roles')->where('slug','super-admin')->value('id');
        if($superId && Schema::hasTable('admins')){
            DB::table('admins')->whereNull('crm_access_role_id')->update([
                'crm_access_role_id'=>$superId,
                'crm_deleted_at'=>null,
            ]);
        }
    }

    public function down(): void
    {
        // Non-destructive rollback for access control data. User/account history is intentionally retained.
    }
};
