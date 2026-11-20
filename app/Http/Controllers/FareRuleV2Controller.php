<?php

namespace App\Http\Controllers;

use App\Services\CrmAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FareRuleV2Controller extends Controller
{
    private function guard(Request $request)
    {
        $adminId = (int) $request->session()->get('crm_admin_id', 0);

        if ($adminId <= 0) {
            return redirect()->route('crm.login');
        }

        try {
            $allowed = app(CrmAccessService::class)
                ->can($adminId, 'fare_rules_v2.view');
        } catch (\Throwable $e) {
            $allowed = false;
        }

        if (!$allowed) {
            abort(403, 'Fare Rules V2 access denied');
        }

        return null;
    }

    public function index(Request $request)
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        $rules = DB::table('fare_rules_v2')
            ->select(
                'id',
                'rule_code',
                'name',
                'status',
                'priority',
                'current_version',
                'stackable',
                'stop_processing',
                'updated_at'
            )
            ->orderBy('priority')
            ->orderBy('id')
            ->paginate(25);

        return view('crm.fare_rules_v2.index', [
            'rules' => $rules,
        ]);
    }

    public function storeDraft(Request $request)
    {
        $allowedEnvironment = (
            config('app.env') === 'staging' &&
            DB::connection()->getDatabaseName() === 'fareshunt_v2_staging'
        ) || (
            config('app.env') === 'production' &&
            DB::connection()->getDatabaseName() === 'travelnbreakz'
        );

        if (!$allowedEnvironment) {
            abort(403, 'Fare Rules V2 environment not authorized');
        }

        $adminId = (int) $request->session()->get('crm_admin_id', 0);

        if ($adminId <= 0) {
            return redirect()->route('crm.login');
        }

        try {
            $allowed = app(CrmAccessService::class)
                ->can($adminId, 'fare_rules_v2.manage');
        } catch (\Throwable $e) {
            $allowed = false;
        }

        if (!$allowed) {
            abort(403, 'V2 rule management access denied');
        }

        $data = $request->validate([
            'rule_code' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9_-]+$/'],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', 'integer', 'between:1,10000'],
            'stackable' => ['required', 'boolean'],
            'stop_processing' => ['required', 'boolean'],
            'conditions' => ['required', 'array', 'min:1', 'max:30'],
            'conditions.*.field' => [
                'required', 'in:airline,origin,destination,cabin,supplier,travel_date,booking_date,trip_type'
            ],
            'conditions.*.operator' => [
                'required', 'in:equals,not_equals,in,not_in,between'
            ],
            'conditions.*.value' => ['required'],
            'conditions.*.condition_group' => ['required', 'integer', 'between:1,10'],
            'actions' => ['required', 'array', 'min:1', 'max:10'],
            'actions.*.action_type' => [
                'required', 'in:percentage_markup,fixed_markup,percentage_discount,fixed_discount'
            ],
            'actions.*.amount' => ['required', 'numeric', 'between:0,1000000'],
        ]);

        foreach ($data['conditions'] as $condition) {
            $operator = $condition['operator'];
            $value = $condition['value'];

            if (in_array($operator, ['in', 'not_in', 'between'], true)) {
                if (!is_array($value) || !$value ||
                    ($operator === 'between' && count($value) !== 2)) {
                    abort(422, 'Invalid condition array');
                }
            } elseif (!is_scalar($value)) {
                abort(422, 'Invalid condition value');
            }

            $values = is_array($value) ? $value : [$value];

            foreach ($values as $item) {
                if (!is_scalar($item) || trim((string) $item) === '') {
                    abort(422, 'Invalid condition item');
                }
            }

            if ($operator === 'between' &&
                strtoupper(trim((string) $value[0])) >
                strtoupper(trim((string) $value[1]))) {
                abort(422, 'Invalid condition range');
            }
        }

        foreach ($data['actions'] as $action) {
            if (str_starts_with($action['action_type'], 'percentage_') &&
                (float) $action['amount'] > 100) {
                abort(422, 'Percentage cannot exceed 100');
            }
        }

        $ruleId = DB::transaction(function () use ($data, $adminId) {
            $now = now();

            $ruleId = DB::table('fare_rules_v2')->insertGetId([
                'rule_code' => $data['rule_code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => 'draft',
                'priority' => $data['priority'],
                'current_version' => 1,
                'stackable' => $data['stackable'],
                'stop_processing' => $data['stop_processing'],
                'created_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $versionId = DB::table('fare_rule_versions_v2')->insertGetId([
                'fare_rule_id' => $ruleId,
                'version_number' => 1,
                'configuration' => json_encode([
                    'conditions' => $data['conditions'],
                    'actions' => $data['actions'],
                ], JSON_THROW_ON_ERROR),
                'status' => 'draft',
                'created_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($data['conditions'] as $index => $condition) {
                DB::table('fare_rule_conditions_v2')->insert([
                    'fare_rule_version_id' => $versionId,
                    'condition_group' => $condition['condition_group'],
                    'sort_order' => $index,
                    'field' => $condition['field'],
                    'operator' => $condition['operator'],
                    'value' => json_encode($condition['value'], JSON_THROW_ON_ERROR),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ($data['actions'] as $index => $action) {
                DB::table('fare_rule_actions_v2')->insert([
                    'fare_rule_version_id' => $versionId,
                    'sort_order' => $index,
                    'action_type' => $action['action_type'],
                    'calculation_base' => 'base_fare',
                    'amount' => $action['amount'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return $ruleId;
        });

        if (!$request->expectsJson()) {
            return redirect()
                ->route('crm.fare_rules_v2')
                ->with('v2_success', 'Draft rule #' . $ruleId . ' created successfully.');
        }

        return response()->json([
            'success' => true,
            'rule_id' => $ruleId,
            'status' => 'draft',
            'message' => 'Fare Rule V2 draft created',
        ], 201);
    }

}
