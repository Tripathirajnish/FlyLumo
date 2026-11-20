<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CrmAccessService
{
    public const PERMISSIONS = [
        'dashboard.view' => ['Dashboard', 'View dashboard and operational KPIs'],

        'bookings.view' => ['Bookings', 'View bookings and booking details'],
        'bookings.edit' => ['Bookings', 'Edit booking status, assignment, notes and general booking data'],
        'passengers.manage' => ['Bookings', 'Add, edit and remove passengers'],
        'itinerary.manage' => ['Bookings', 'Add, edit and remove itinerary segments'],
        'fares.manage' => ['Bookings', 'Reprice bookings and update fare information'],

        'customers.view' => ['Customers', 'View customer records'],
        'customers.edit' => ['Customers', 'Edit customer profiles and activity'],

        'leads.view' => ['Leads', 'View leads and quotations'],
        'leads.edit' => ['Leads', 'Edit leads, activities and quotations'],

        'payments.view' => ['Payments', 'View payment records and payment metadata'],
        'payments.edit' => ['Payments', 'Update payment operations and activity'],

        'refunds.view' => ['Refunds', 'View refund cases and refund history'],
        'refunds.request' => ['Refunds', 'Create refund requests and add refund case activity'],
        'refunds.approve' => ['Refunds', 'Approve, process and close refund cases'],

        'chargebacks.view' => ['Chargebacks', 'View chargeback cases, evidence and history'],
        'chargebacks.manage' => ['Chargebacks', 'Create, update, submit and resolve chargeback cases'],

        'communications.view' => ['Communications', 'View communication records and previews'],
        'communications.send' => ['Communications', 'Send/resend passenger emails and quotations'],

        'followups.view' => ['Follow-ups', 'View actionable follow-ups'],
        'followups.manage' => ['Follow-ups', 'Create, edit, complete, copy and delete follow-ups'],

        'authorizations.view' => ['Authorization', 'View authorization status, evidence and documents'],
        'authorizations.manage' => ['Authorization', 'Create, resend and revoke authorization requests'],

        'fare_rules.view' => ['Fare Rules', 'View fare rules, history and tests'],
        'fare_rules.manage' => ['Fare Rules', 'Create, edit, activate, duplicate and delete fare rules'],
        'fare_rules_v2.view' => ['Fare Rules V2', 'View Enterprise Fare Rules V2 drafts'],
        'fare_rules_v2.manage' => ['Fare Rules V2', 'Create Enterprise Fare Rules V2 drafts'],
        'fare_rules_v2.approve' => ['Fare Rules V2', 'Independently approve or reject fare rule changes'],

        'reports.view' => ['Reports', 'View reports and analytics'],
        'reports.export' => ['Reports', 'Export CRM reports'],

        'profit_report.view' => ['Profit Report', 'View Profit Report records permitted for this CRM user'],
        'profit_report.export' => ['Profit Report', 'Export permitted Profit Report records'],
        'profit_report.edit' => ['Profit Report', 'Edit Profit Report financial and issuance fields'],

        'notifications.view' => ['Notifications', 'View and mark CRM notifications'],

        'email_templates.view' => ['Email Templates', 'View and preview email templates'],
        'email_templates.manage' => ['Email Templates', 'Create, edit, duplicate, activate and delete templates'],

        'api_control.view' => ['API Control', 'View supplier/API configuration and diagnostics'],
        'api_control.manage' => ['API Control', 'Change Demo/Live mode, credentials and connection settings'],

        'stripe_gateway.view' => ['Payment Gateway', 'View Stripe payment gateway status (Super Admin only)'],
        'stripe_gateway.manage' => ['Payment Gateway', 'Turn Stripe checkout ON/OFF per booking flow (Super Admin only)'],

        'tracking.view' => ['Tracking', 'View booking activity/audit tracking'],
        'tracking.add_note' => ['Tracking', 'Add append-only tracking notes'],

        'website_analytics.view' => ['Website Analytics', 'View website/search analytics'],
        'google_analytics.view' => ['Google Analytics', 'View Google Analytics reporting'], 
        
        'live_pax.view' => ['Live Pax Monitor', 'View live anonymous visitor sessions and privacy-safe replays'],

        'cms.view' => ['CMS', 'View website CMS and content managers'],
        'cms.manage' => ['CMS', 'Edit website settings, pages, footer and deal cards'],

        'settings.view' => ['Settings', 'View CRM settings and agency information'],
        'settings.manage' => ['Settings', 'Edit public website contact settings'],

        'users.view' => ['Users & Access', 'View CRM users, roles and permissions'],
        'users.manage' => ['Users & Access', 'Create/edit/deactivate/delete users and manage access'],

        'trash.view' => ['CRM Trash', 'View archived CRM records'],
        'trash.manage' => ['CRM Trash', 'Restore/archive CRM records'],

        'records.manage' => ['Universal Record Actions', 'Use generic edit/archive actions when no module-specific permission applies'],
        'records.permanent_delete' => ['Permanent Delete', 'Permanently delete CRM records; super-admin only'],

        'travel_offers.view' => ['Travel Offers', 'View travel offers and customer-ready previews'],
        'travel_offers.manage' => ['Travel Offers', 'Create and edit reusable promotional travel offers'],

        'travel_plans.view' => ['Travel Plans', 'View customer travel plans and trip entitlements'],
        'travel_plans.manage' => ['Travel Plans', 'Manage customer travel plans and trip entitlements'],

        'master_data.view' => ['Travel Master Data', 'View countries, cities, airports and airlines'],
        'master_data.manage' => ['Travel Master Data', 'Create, edit and safely delete travel master data'],
    ];

    public static function groups(): array
    {
        $out = [];
        foreach (self::PERMISSIONS as $key => [$group, $description]) {
            $out[$group][$key] = $description;
        }
        return $out;
    }

    public function ready(): bool
    {
        return Schema::hasTable('crm_access_roles')
            && Schema::hasTable('crm_access_role_permissions')
            && Schema::hasColumn('admins', 'crm_access_role_id');
    }

    public function can(?int $adminId, string $permission): bool
    {
        if (!$adminId || !array_key_exists($permission, self::PERMISSIONS)) return false;

        // Fail closed: incomplete schema must never grant CRM permissions. Deploy migrations
        // and assign explicit roles before enabling CRM access.
        if (!$this->ready()) return false;

        $admin = DB::table('admins')->where('id', $adminId)->first();
        if (!$admin) return false;
        if ((string)($admin->status ?? '0') !== '1') return false;
        if (property_exists($admin, 'crm_deleted_at') && $admin->crm_deleted_at) return false;

        $roleId = (int)($admin->crm_access_role_id ?? 0);
        if (!$roleId) return false;

        $role = DB::table('crm_access_roles')->where('id', $roleId)->first();
        if (!$role || !(int)$role->is_active) return false;

        // Permanent deletion is never granted through ordinary role permissions.
        if ($permission === 'records.permanent_delete') return $role->slug === 'super-admin';
        if ($role->slug === 'super-admin') return true;

        if (Schema::hasTable('crm_admin_permission_overrides')) {
            $override = DB::table('crm_admin_permission_overrides')
                ->where('admin_id', $adminId)
                ->where('permission_key', $permission)
                ->value('allowed');
            if ($override !== null) return (bool)$override;
        }

        return DB::table('crm_access_role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_key', $permission)
            ->exists();
    }

    public function rolePermissions(int $roleId): array
    {
        if (!$this->ready()) return [];
        $role = DB::table('crm_access_roles')->where('id', $roleId)->first();
        if ($role && $role->slug === 'super-admin') return array_keys(self::PERMISSIONS);

        return DB::table('crm_access_role_permissions')
            ->where('role_id', $roleId)
            ->pluck('permission_key')
            ->all();
    }

    public function effectivePermissions(int $adminId): array
    {
        $keys = array_keys(self::PERMISSIONS);
        return array_values(array_filter($keys, fn($p) => $this->can($adminId, $p)));
    }

    public function roleName(?int $adminId): string
    {
        if (!$adminId || !$this->ready()) return 'CRM Admin';
        return (string) DB::table('admins as a')
            ->leftJoin('crm_access_roles as r', 'a.crm_access_role_id', '=', 'r.id')
            ->where('a.id', $adminId)
            ->value('r.name') ?: 'CRM Admin';
    }

    public function permissionForRequest(Request $request): ?string
    {
        $name = (string) optional($request->route())->getName();

        if (str_starts_with($name, 'legacy.crm.')) {
            $name = substr($name, strlen('legacy.'));
        }

        if ($name === '' || !str_starts_with($name, 'crm.')) return null;

        // Authentication routes are always allowed.
        if (in_array($name, ['crm.login','crm.authenticate','crm.logout'], true)) return null;

        // User & role management.
        if ($name === 'crm.team_access') return 'users.view';
        if (str_starts_with($name, 'crm.user_') || str_starts_with($name, 'crm.role_')) return 'users.manage';

        if ($name === 'crm.dashboard') return 'dashboard.view';

        // Explicit mappings for legacy and newer CRM modules. Unknown CRM routes are denied below.
        if ($name === 'crm.destinations_manager') return 'cms.view';
        if (in_array($name, ['crm.destination_create','crm.destination_edit','crm.destination_store','crm.destination_update','crm.destination_delete'], true)) return 'cms.manage';
        if ($name === 'crm.integrations') return 'api_control.view';
        if ($name === 'crm.integrations.store') return 'api_control.manage';
        if (in_array($name, ['crm.travel_desk','crm.travel_desk.show'], true)) return 'bookings.view';
        if (in_array($name, ['crm.travel_desk.create','crm.travel_desk.store'], true)) return 'bookings.edit';
        if ($name === 'crm.travel_guides_manager') return 'travel_offers.view';
        if (in_array($name, ['crm.travel_guide_create','crm.travel_guide_edit','crm.travel_guide_store','crm.travel_guide_update','crm.travel_guide_delete'], true)) return 'travel_offers.manage';
        if (in_array($name, ['crm.invoice_show'], true)) return 'bookings.view';
        if (in_array($name, ['crm.invoice_create','crm.invoice_store'], true)) return 'bookings.edit';
        if (in_array($name, ['crm.incomplete_bookings','crm.incomplete_booking_detail'], true)) return 'bookings.view';
        if ($name === 'crm.incomplete_booking_delete') return 'bookings.edit';
        if ($name === 'crm.incomplete_booking_email_send') return 'communications.send';
        if ($name === 'crm.universal_permanent_delete') return 'records.permanent_delete';
        // 7 Nov 2026: these routes were unmapped, so they returned 403 even for Super Admin.
        if (in_array($name, ['crm.manual_booking_create','crm.manual_booking_store'], true)) return 'bookings.edit';
        if ($name === 'crm.meta_monitor') return 'website_analytics.view';

        if (in_array($name, ['crm.bookings','crm.booking_detail'], true)) return 'bookings.view';
        if (in_array($name, ['crm.booking_passenger_add','crm.booking_passenger_update','crm.booking_passenger_delete'], true)) return 'passengers.manage';
        if (in_array($name, ['crm.booking_segment_add','crm.booking_segment_update','crm.booking_segment_delete'], true)) return 'itinerary.manage';
        if ($name === 'crm.booking_reprice') return 'fares.manage';
        if ($name === 'crm.booking_card_metadata') return 'payments.edit';
        if (in_array($name, [
            'crm.booking_email_preview','crm.booking_email_resend',
            'crm.booking_template_email_preview','crm.booking_template_email_send'
        ], true)) return $name === 'crm.booking_template_email_send' || $name === 'crm.booking_email_resend'
            ? 'communications.send' : 'communications.view';
        if (str_starts_with($name, 'crm.booking_')) return 'bookings.edit';

        if (in_array($name, ['crm.customers','crm.customer_detail'], true)) return 'customers.view';
        if (str_starts_with($name, 'crm.customer_')) return 'customers.edit';

        if (in_array($name, ['crm.leads','crm.lead_detail'], true)) return 'leads.view';
        if (str_starts_with($name, 'crm.lead_')) return 'leads.edit';

        if (in_array($name, ['crm.payments','crm.payment_detail'], true)) return 'payments.view';
        if (str_starts_with($name, 'crm.payment_')) return 'payments.edit';

        if (in_array($name, ['crm.refunds','crm.refund_detail'], true)) return 'refunds.view';
        if (in_array($name, ['crm.refund_create','crm.refund_store'], true)) return 'refunds.request';
        if (str_starts_with($name, 'crm.refund_')) return 'refunds.approve';

        if (in_array($name, ['crm.chargebacks','crm.chargeback_detail','crm.chargeback_document'], true)) return 'chargebacks.view';
        if (str_starts_with($name, 'crm.chargeback_')) return 'chargebacks.manage';

        if (in_array($name, ['crm.communications','crm.communication_detail','crm.communication_quotation_print'], true)) return 'communications.view';
        if (str_starts_with($name, 'crm.communication_')) return 'communications.send';

        if (in_array($name, ['crm.followups','crm.followups_v2'], true)) return 'followups.view';
        if (str_starts_with($name, 'crm.followup_')) return 'followups.manage';

        if (in_array($name, ['crm.authorization_center','crm.authorization_evidence','crm.authorization_document'], true)) return 'authorizations.view';
        if (str_starts_with($name, 'crm.authorization_')) return 'authorizations.manage';

        if ($name === 'crm.fare_rules_v2') return 'fare_rules_v2.view';
        if ($name === 'crm.fare_rules_v2.drafts.store') return 'fare_rules_v2.manage';

        if (in_array($name, ['crm.fare_rule_change_approve', 'crm.fare_rule_change_reject'], true)) return 'fare_rules_v2.approve';

        if ($name === 'crm.fare_rules' || $name === 'crm.fare_rule_history' || $name === 'crm.fare_rule_test') return 'fare_rules.view';
        if (str_starts_with($name, 'crm.fare_rule_')) return 'fare_rules.manage';

        if ($name === 'crm.reports') return 'reports.view';
        if (in_array($name, ['crm.reports_export','crm.export'], true)) return 'reports.export';

        if ($name === 'crm.profit_report') return 'profit_report.view';
        if ($name === 'crm.profit_report_export') return 'profit_report.export';
        if ($name === 'crm.profit_report_update') return 'profit_report.edit';

        if (str_starts_with($name, 'crm.notification')) return 'notifications.view';

        if (in_array($name, ['crm.email_templates','crm.email_template_preview'], true)) return 'email_templates.view';
        if (str_starts_with($name, 'crm.email_template_')) return 'email_templates.manage';

        if (in_array($name, ['crm.api_control','crm.api_control_v2','crm.api_control_v3','crm.api_control_v3_1'], true)) return 'api_control.view';
        if (str_starts_with($name, 'crm.api_control_')) return 'api_control.manage';

        if (in_array($name, ['crm.tracking','crm.tracking_v2'], true)) return 'tracking.view';
        if ($name === 'crm.tracking_v2_manual') return 'tracking.add_note';

        if ($name === 'crm.website_analytics') return 'website_analytics.view';
        if ($name === 'crm.google_analytics') return 'google_analytics.view';
        
        if (str_starts_with($name, 'crm.live_pax')) return 'live_pax.view';

        if (in_array($name, ['crm.cms','crm.pages_manager','crm.page_edit','crm.page_create','crm.content_manager','crm.footer_manager'], true)) return 'cms.view';
        if (str_starts_with($name, 'crm.cms_') || str_starts_with($name, 'crm.page_') || str_starts_with($name, 'crm.content_manager_') || str_starts_with($name, 'crm.footer_manager_')) return 'cms.manage';

        // Stripe payment gateway panel (controller also enforces Super Admin).
        if ($name === 'crm.stripe_gateway') return 'stripe_gateway.view';
        if ($name === 'crm.stripe_gateway_update') return 'stripe_gateway.manage';

        if ($name === 'crm.settings') return 'settings.view';
        if (str_starts_with($name, 'crm.settings_')) return 'settings.manage';

        if (in_array($name, ['crm.travel_offers', 'crm.travel_offers.preview'], true)) return 'travel_offers.view';
        if (str_starts_with($name, 'crm.travel_offers.')) return 'travel_offers.manage';

        if (in_array($name, ['crm.travel_plans', 'crm.travel_plans.show'], true)) return 'travel_plans.view';
        if (str_starts_with($name, 'crm.travel_plans.')) return 'travel_plans.manage';

        if ($name === 'crm.master_data') return 'master_data.view';
        if (str_starts_with($name, 'crm.master_data_')) return 'master_data.manage';

        if ($name === 'crm.universal_trash') return 'trash.view';

        if (in_array($name, ['crm.universal_field_update','crm.universal_archive','crm.universal_restore'], true)) {
            $module = strtolower((string)$request->input('module', $request->query('module', '')));
            return match ($module) {
                'booking' => 'bookings.edit',
                'customer' => 'customers.edit',
                'lead' => 'leads.edit',
                'payment' => 'payments.edit',
                default => $name === 'crm.universal_restore' || $name === 'crm.universal_archive'
                    ? 'trash.manage'
                    : 'records.manage',
            };
        }

        return '__unmapped_crm_route__';
    }
}