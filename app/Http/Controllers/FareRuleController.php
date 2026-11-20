<?php

namespace App\Http\Controllers;

use App\Models\Markup;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class FareRuleController extends Controller
{
    private function phase2Ready(): bool
    {
        try
        {
            return (bool) \Illuminate\Support\Facades\Cache::remember('markups_phase2_cols', 60, function () {
                return Schema::hasColumn('markups','rule_action') && Schema::hasColumn('markups','adv_min_days') && Schema::hasColumn('markups','adv_max_days');
            });
        }
        catch (\Throwable $e)
        {
            return false;
        }
    }

    // Login check (unchanged) + the project's existing CRM permission system (CrmAccessService::can).
    // Same permission keys the CrmPermissionGate middleware already maps for these routes:
    //   fare_rules.view   -> list, history (audit), simulator
    //   fare_rules.manage -> create, store, edit, update, toggle (activate/deactivate), duplicate, delete
    // Super Admin is allowed by CrmAccessService itself. No new permission keys, no separate auth.
    private function guard(Request $request, string $permission = 'fare_rules.view')
    {
        if (!$request->session()->has('crm_admin_id')) {
            return redirect()->route('crm.login');
        }

        $allowed = false;
        try
        {
            $allowed = app(\App\Services\CrmAccessService::class)->can((int) $request->session()->get('crm_admin_id'), $permission);
        }
        catch (\Throwable $e)
        {
            $allowed = false; // if the check cannot run, deny (never weaken access)
        }

        if (!$allowed) {
            abort(403, 'You do not have permission to perform this CRM action.');
        }
        return null;
    }

    public function index(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $query = Markup::query();

        if ($request->filled('q')) {
            $term = trim((string)$request->input('q'));
            $query->where(function ($w) use ($term) {
                $w->where('name','like',"%{$term}%")
                  ->orWhere('departure_code','like',"%{$term}%")
                  ->orWhere('arrival_code','like',"%{$term}%")
                  ->orWhere('airline_code','like',"%{$term}%");
                if (ctype_digit($term)) $w->orWhere('id',(int)$term);
            });
        }

        if ($request->filled('status')) $query->where('is_active',$request->input('status'));

        if ($request->filled('source')) {
            if ($request->input('source') === 'website') $query->where('is_ownsite','Yes');
            if ($request->input('source') === 'metasite') $query->where('is_facecompare','Yes');
        }

        if ($request->filled('journey')) {
            $field = match($request->input('journey')) { 'one_way'=>'is_oneway', 'multi_city'=>'is_multicity', default=>'is_roundtrip' };
            $query->where($field,'Yes');
        }

        if ($request->filled('route_scope')) {
            if ($request->input('route_scope') === 'airport') {
                $query->where('departure_type','airport')->where('arrival_type','airport');
            } elseif ($request->input('route_scope') === 'country') {
                $query->where('departure_type','country')->where('arrival_type','country');
            } elseif ($request->input('route_scope') === 'mixed') {
                $query->whereColumn('departure_type','!=','arrival_type');
            }
        }

        if ($request->filled('adjustment')) $query->where('amt_per',$request->input('adjustment'));
        if ($request->filled('airline')) {
            if ($request->input('airline') === 'specific') {
                $query->whereNotNull('airline_code')->where('airline_code','!=','');
            } elseif ($request->input('airline') === 'all') {
                $query->where(function($q){ $q->whereNull('airline_code')->orWhere('airline_code',''); });
            }
        }

        if ($request->filled('health')) {
            $today = now()->toDateString();
            $soon = now()->addDays(7)->toDateString();
            if ($request->input('health') === 'expired') {
                $query->whereDate('to_date','<',$today);
            } elseif ($request->input('health') === 'expiring') {
                $query->whereDate('to_date','>=',$today)->whereDate('to_date','<=',$soon);
            } elseif ($request->input('health') === 'current') {
                $query->whereDate('from_date','<=',$today)->whereDate('to_date','>=',$today);
            }
        }

        $perPage=(int)$request->input('per_page',20);
        if(!in_array($perPage,[15,20,25,50,100],true)) $perPage=20;

        $rules=$query->orderByDesc('is_active')->orderByDesc('id')->paginate($perPage)->withQueryString();

        $allActive=Markup::where('is_active','Yes')->get();
        $conflictMap=[];
        foreach($rules->items() as $rule) {
            $conflictMap[$rule->id]=$this->findConflicts($rule,$allActive)->count();
        }

        $today=now()->toDateString();
        $soon=now()->addDays(7)->toDateString();
        $stats=[
            'total'=>Markup::count(),
            'active'=>Markup::where('is_active','Yes')->count(),
            'inactive'=>Markup::where('is_active','No')->count(),
            'expired'=>Markup::whereNotNull('to_date')->whereDate('to_date','<',$today)->count(),
            'expiring'=>Markup::where('is_active','Yes')->whereDate('to_date','>=',$today)->whereDate('to_date','<=',$soon)->count(),
            'airline'=>Markup::whereNotNull('airline_code')->where('airline_code','!=','')->count(),
            'website'=>Markup::where('is_ownsite','Yes')->count(),
            'metasite'=>Markup::where('is_facecompare','Yes')->count(),
        ];

        return view('crm.fare-rules',compact('rules','stats','conflictMap'));
    }

    public function create(Request $request)
    {
        if ($redirect = $this->guard($request, 'fare_rules.manage')) return $redirect;
        return view('crm.fare-rule-form',['rule'=>null,'conflicts'=>collect(),'history'=>collect()]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->guard($request, 'fare_rules.manage')) return $redirect;
        $data=$this->validateRule($request);
        $rule=Markup::create($data);
        $this->audit($request,$rule->id,'created','Fare rule created',null,$rule->fresh()->toArray());
        return redirect()->route('crm.fare_rule_edit',$rule->id)->with('success','Fare rule created. Review conflicts and run the simulator before activating live traffic.');
    }

    public function edit(Request $request,int $id)
    {
        if ($redirect = $this->guard($request, 'fare_rules.manage')) return $redirect;
        $rule=Markup::findOrFail($id);
        $conflicts=$this->findConflicts($rule,Markup::where('is_active','Yes')->get());
        $history=$this->historyRows($id,8);
        return view('crm.fare-rule-form',compact('rule','conflicts','history'));
    }

    public function update(Request $request,int $id)
    {
        if ($redirect = $this->guard($request, 'fare_rules.manage')) return $redirect;
        $rule=Markup::findOrFail($id);
        $before=$rule->toArray();
        $rule->update($this->validateRule($request));
        $after=$rule->fresh()->toArray();
        $this->audit($request,$id,'updated','Fare rule updated',$before,$after);
        return redirect()->route('crm.fare_rule_edit',$id)->with('success','Fare rule updated successfully.');
    }

    public function toggle(Request $request,int $id)
    {
        if ($redirect = $this->guard($request, 'fare_rules.manage')) return $redirect;
        $rule=Markup::findOrFail($id);
        $before=$rule->toArray();
        $rule->is_active=in_array($rule->is_active,['Yes','1'],true)?'No':'Yes';
        $rule->save();
        $this->audit($request,$id,'status_changed','Rule status changed to '.$rule->is_active,$before,$rule->fresh()->toArray());
        return back()->with('success','Fare rule status updated.');
    }

    public function duplicate(Request $request,int $id)
    {
        if ($redirect = $this->guard($request, 'fare_rules.manage')) return $redirect;
        $source=Markup::findOrFail($id);
        $copy=$source->replicate();
        $copy->name=$source->name.' - Copy';
        $copy->is_active='No';
        $copy->save();
        $this->audit($request,$copy->id,'duplicated','Rule duplicated from #'.$source->id,null,$copy->toArray());
        return redirect()->route('crm.fare_rule_edit',$copy->id)->with('success','Rule duplicated as an inactive copy. Review it before activation.');
    }

    public function destroy(Request $request,int $id)
    {
        if ($redirect = $this->guard($request, 'fare_rules.manage')) return $redirect;
        $rule=Markup::findOrFail($id);
        $before=$rule->toArray();
        $this->audit($request,$id,'deleted','Fare rule deleted',$before,null);
        $rule->delete();
        return back()->with('success','Fare rule deleted.');
    }

    public function history(Request $request,int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $rule=Markup::findOrFail($id);
        $history=$this->historyRows($id,100);
        return view('crm.fare-rule-history',compact('rule','history'));
    }

    public function simulator(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        // Open simulator safely, including when a fare rule pre-fills route values.
        // Validation should run only after Run Simulation is explicitly submitted.
        if (!$request->boolean('run_simulation')) {
            return view('crm.fare-rule-test');
        }

        $data=$request->validate([
            'source'=>['required','in:website,metasite'],
            'departure_code'=>['required','string','max:10'],
            'departure_country'=>['nullable','string','max:3'],
            'arrival_code'=>['required','string','max:10'],
            'arrival_country'=>['nullable','string','max:3'],
            'airline_code'=>['nullable','string','max:10'],
            'journey'=>['required','in:one_way,round_trip,multi_city'],
            'cabin'=>['required','in:Econom,Peconom,Business,firstclass'],
            'adult_fare'=>['required','numeric','min:0'],
            'child_fare'=>['nullable','numeric','min:0'],
            'infant_fare'=>['nullable','numeric','min:0'],
            'adults'=>['required','integer','min:1'],
            'children'=>['nullable','integer','min:0'],
            'infants'=>['nullable','integer','min:0'],
            'travel_date'=>['required','date'],
            'return_date'=>['nullable','date','after_or_equal:travel_date'],
        ]);

        foreach(['departure_code','departure_country','arrival_code','arrival_country','airline_code'] as $f) {
            $data[$f]=strtoupper(trim((string)($data[$f]??'')));
        }

        if($data['source']==='metasite' && $data['journey']==='multi_city'){
            throw ValidationException::withMessages(['journey'=>'FareLeader / Meta Site does not support multi-city searches.']);
        }

        $departureDate=$data['travel_date'];
        $returnDate=$data['journey']==='round_trip'
            ? ($data['return_date'] ?: $departureDate)
            : $departureDate;

        $input=$this->simulationInput($data,$departureDate,$returnDate);

        // The simulator runs the REAL live engine code (no separate approximation of the pricing):
        //   Website  -> FlightController::fetchMarkupRows() + isAirlineBlocked() + addReduceMarkupArray()
        //   FareLeader -> FareleaderService::checkMarkup()  + isAirlineBlocked() + addReduceMarkupArray()
        $engine=$data['source']==='metasite'
            ? app(\App\Services\FareleaderService::class)
            : app(\App\Http\Controllers\FlightController::class);
        $rows=$data['source']==='metasite' ? $engine->checkMarkup($input['fields']) : $engine->fetchMarkupRows($input['fields']);
        $rows=is_array($rows)?array_values($rows):[];

        $ids=array_values(array_filter(array_map(fn($r)=>(int)($r['id']??0),$rows)));
        $models=$ids ? Markup::whereIn('id',$ids)->get()->keyBy('id')->all() : [];

        $sim=$this->computeSimulation($engine,$rows,$models,$data,$input);

        $evaluated=collect($sim['evaluated']);
        $result=$sim['result'];

        $diagnostics=[
            'channel'=>$data['source']==='metasite'?'Meta Site / FareLeader':'Website / Own Site',
            'route'=>$data['departure_code'].' → '.$data['arrival_code'],
            'airline'=>$data['airline_code']?:'Any airline',
            'journey'=>['one_way'=>'One Way','round_trip'=>'Round Trip','multi_city'=>'Multi-City'][$data['journey']],
            'cabin'=>['Econom'=>'Economy','Peconom'=>'Premium Economy','Business'=>'Business','firstclass'=>'First Class'][$data['cabin']],
            'travel_date'=>$departureDate,
            'return_date'=>$returnDate,
            'candidate_count'=>count($rows),
            'blocked_by'=>$sim['blocked_by'],
        ];

        return view('crm.fare-rule-test',compact('data','evaluated','result','diagnostics'));
    }

    // Turns the simulator form into the exact inputs the live engines receive.
    private function simulationInput(array $data,string $departureDate,string $returnDate): array
    {
        $isMeta=$data['source']==='metasite';

        $flightType=$data['journey'];
        $cabin=$data['cabin'];
        if($isMeta){
            // FareLeader sends 'oneway' / 'round_trip' and 'PremiumEconomy' / 'First' (see FareleaderService::getFormField).
            $flightType=$data['journey']==='one_way'?'oneway':'round_trip';
            $cabin=['Econom'=>'Econom','Peconom'=>'PremiumEconomy','Business'=>'Business','firstclass'=>'First'][$data['cabin']];
        }

        $adult=(float)$data['adult_fare'];
        $child=(float)($data['child_fare']??0);
        $infant=(float)($data['infant_fare']??0);
        $nAdult=(int)$data['adults'];
        $nChild=(int)($data['children']??0);
        $nInfant=(int)($data['infants']??0);

        return [
            'fields'=>[
                'FlightType'=>$flightType,
                'FlightClass'=>$cabin,
                'IATAFrom'=>$data['departure_code'],
                'IATATo'=>$data['arrival_code'],
                'IATAFromC'=>$data['departure_country']??'',
                'IATAToC'=>$data['arrival_country']??'',
                'DeptDate'=>date('d.m.Y',strtotime($departureDate)),
                'RetnDate'=>$data['journey']==='round_trip'?date('d.m.Y',strtotime($returnDate)):'',
            ],
            'searchField'=>['Adults'=>$nAdult,'Childrens'=>$nChild,'Infants'=>$nInfant,'Currency'=>'USD'],
            'fareData'=>[
                'AdultPrice'=>(string)$adult,
                'ChildPrice'=>$child>0?(string)$child:'',
                'InfantPrice'=>(string)$infant,
                'TotalPrice'=>(string)(($adult*$nAdult)+($child*$nChild)+($infant*$nInfant)),
            ],
            'adult'=>$adult,'child'=>$child,'infant'=>$infant,
            'nAdult'=>$nAdult,'nChild'=>$nChild,'nInfant'=>$nInfant,
        ];
    }

    // Prices the matched rules by calling the live engine's own addReduceMarkupArray()/isAirlineBlocked().
    // Pure function of its arguments (no database access) so it can be tested directly.
    private function computeSimulation($engine,array $rows,array $models,array $data,array $input): array
    {
        $airline=(string)($data['airline_code']??'');
        $sf=$input['searchField'];
        $fd=$input['fareData'];
        $action=fn($r)=>($r['rule_action']??'discount');

        // Same call the live search makes: a blocked airline removes the offer.
        $blocked=$engine->isAirlineBlocked($rows,[$airline]);
        $blockRows=array_values(array_filter($rows,fn($r)=>$action($r)==='block' && $engine->isAirlineBlocked([$r],[$airline])));
        $priceRows=array_values(array_filter($rows,fn($r)=>$action($r)!=='block'));

        $baseTotal=($input['adult']*$input['nAdult'])+($input['child']*$input['nChild'])+($input['infant']*$input['nInfant']);

        $evaluated=[];
        foreach($priceRows as $row){
            $one=$engine->addReduceMarkupArray($sf,$fd,[$row],$airline);
            $m=$one['MinMarkup']??'';
            if(!is_array($m) || empty($one['MarkupDB'])){
                // Either the rule does not apply to this airline (same filter as live), or a safety guard
                // (zero-fare guard) skipped it. Show the guarded case so nobody is surprised.
                $codes=array_values(array_filter(array_map(fn($c)=>strtoupper(trim($c)),explode(',',(string)($row['airline_code']??'')))));
                $airOk=empty($codes) || ($airline!=='' && in_array(strtoupper($airline),$codes,true));
                if($airOk){
                    $evaluated[(int)$row['id']]=[
                        'rule'=>$models[(int)$row['id']] ?? (object)$row,
                        'adult_base'=>$input['adult'],'child_base'=>$input['child'],'infant_base'=>$input['infant'],
                        'new_adult'=>$input['adult'],'new_child'=>$input['child'],'new_infant'=>$input['infant'],
                        'base_total'=>$baseTotal,'final_total'=>$baseTotal,'total_delta'=>0,
                        'specificity'=>0,'skipped'=>true,
                        'warnings'=>['SKIPPED: this rule would make a passenger fare $0 or negative - not used for this fare.'],
                    ];
                }
                continue;
            }

            $newAdult=(float)$m['New_AdultPrice'];
            $newChild=(float)($m['New_ChildPrice']??0);
            $newInfant=(float)($m['New_InfantPrice']??($m['InfantPrice']??$input['infant']));
            $finalTotal=(float)$m['TotalPrice'];
            $minFare=(float)($row['fare_min_price']??0);

            $warnings=[];
            if($newAdult<=0) $warnings[]='This rule makes the adult fare zero.';
            if(abs((float)$row['markup'])>=50 && ($row['amt_per']??'')==='per' && $action($row)==='discount') $warnings[]='Large percentage adjustment — verify commercial intent.';
            if($action($row)==='discount' && $minFare>0 && $input['adult']>$minFare && $newAdult<=$minFare) $warnings[]='Minimum fare floor is active for this rule: the discounted fare is held at the rule minimum.';

            $evaluated[(int)$row['id']]=[
                'rule'=>$models[(int)$row['id']] ?? (object)$row,
                'adult_base'=>$input['adult'],'child_base'=>$input['child'],'infant_base'=>$input['infant'],
                'new_adult'=>$newAdult,'new_child'=>$newChild,'new_infant'=>$newInfant,
                'base_total'=>$baseTotal,'final_total'=>$finalTotal,
                'total_delta'=>$baseTotal-$finalTotal,
                'specificity'=>(int)($m['Specificity']??0),'warnings'=>$warnings,
            ];
        }

        // Winner: exactly what the live engine selects from the full rule list.
        $result=null;
        if(!$blocked && $priceRows){
            $all=$engine->addReduceMarkupArray($sf,$fd,$priceRows,$airline);
            $win=is_array($all['MinMarkup']??null) ? (int)$all['MinMarkup']['MarkupId'] : 0;
            if($win && isset($evaluated[$win])){
                $e=$evaluated[$win];
                $result=[
                    'rule'=>$e['rule'],
                    'adult_price'=>$e['new_adult'],
                    'child_price'=>$e['new_child'],
                    'infant_price'=>$e['new_infant'],
                    'base_total'=>$e['base_total'],
                    'total'=>$e['final_total'],
                    'delta_total'=>$e['total_delta'],
                    'specificity'=>$e['specificity'],
                    'warnings'=>$e['warnings'],
                ];
            }
        }

        return [
            'evaluated'=>array_values($evaluated),
            'result'=>$result,
            'blocked_by'=>$blockRows ? implode(', ',array_map(fn($r)=>'#'.$r['id'].' '.($r['name']??''),$blockRows)) : 'Not blocked',
        ];
    }

    private function validateRule(Request $request): array
    {
        $data=$request->validate([
            'name'=>['required','string','max:100'],
            'departure_type'=>['required','in:airport,country'],
            'departure_code'=>['required','string','max:255'],
            'arrival_type'=>['required','in:airport,country'],
            'arrival_code'=>['required','string','max:255'],
            'airline_code'=>['nullable','string','max:255'],
            'markup'=>['required','integer'],
            'amt_per'=>['required','in:amt,per'],
            'is_oneway'=>['nullable','boolean'],
            'is_roundtrip'=>['nullable','boolean'],
            'is_multicity'=>['nullable','boolean'],
            'is_economy'=>['nullable','boolean'],
            'is_peconomy'=>['nullable','boolean'],
            'is_business'=>['nullable','boolean'],
            'is_fclass'=>['nullable','boolean'],
            'is_ownsite'=>['nullable','boolean'],
            'is_facecompare'=>['nullable','boolean'],
            'fare_min_price'=>['nullable','integer','min:0'],
            'from_date'=>['required','date'],
            'to_date'=>['required','date','after_or_equal:from_date'],
            'is_active'=>['required','in:Yes,No'],
        ]);

        foreach(['is_oneway','is_roundtrip','is_multicity','is_economy','is_peconomy','is_business','is_fclass','is_ownsite','is_facecompare'] as $f) {
            $data[$f]=$request->boolean($f)?'Yes':'No';
        }

        foreach(['departure_code','arrival_code','airline_code'] as $f) {
            $data[$f]=$this->normaliseCodes((string)($data[$f]??''));
        }
        $data['is_active']=$request->input('is_active')==='Yes'?'Yes':'No';

        $errors=[];
        if($data['is_ownsite']!=='Yes' && $data['is_facecompare']!=='Yes') $errors['is_ownsite']='Select at least one sales channel.';
        if($data['is_oneway']!=='Yes' && $data['is_roundtrip']!=='Yes' && $data['is_multicity']!=='Yes') $errors['is_oneway']='Select at least one journey type.';
        if(!collect(['is_economy','is_peconomy','is_business','is_fclass'])->contains(fn($f)=>$data[$f]==='Yes')) $errors['is_economy']='Select at least one cabin.';
        if(empty($this->tokens($data['departure_code']))) $errors['departure_code']='Enter at least one departure airport/country code.';
        if(empty($this->tokens($data['arrival_code']))) $errors['arrival_code']='Enter at least one arrival airport/country code.';
        if($data['amt_per']==='per' && abs((int)$data['markup'])>100) $errors['markup']='Percentage adjustment must be between -100% and 100%.';

        // ---- Phase 2 fields (only when the new columns exist in the markups table) ----
        if($this->phase2Ready()){
            $extra=$request->validate([
                'rule_action'=>['nullable','in:discount,fixed,block'],
                'adv_min_days'=>['nullable','integer','min:0','max:365'],
                'adv_max_days'=>['nullable','integer','min:0','max:365'],
            ]);
            $data['rule_action']=$extra['rule_action'] ?? 'discount';
            if(!$data['rule_action']) $data['rule_action']='discount';
            $data['adv_min_days']=$extra['adv_min_days'] ?? null;
            $data['adv_max_days']=$extra['adv_max_days'] ?? null;

            if($data['adv_min_days']!==null && $data['adv_max_days']!==null && (int)$data['adv_max_days']<(int)$data['adv_min_days']){
                $errors['adv_max_days']='Maximum advance days must be equal to or greater than the minimum.';
            }
            if($data['rule_action']==='fixed'){
                if((int)$data['markup']<1) $errors['markup']='Fixed fare must be at least 1 (it is the exact adult fare shown).';
                elseif((int)($data['fare_min_price']??0)>(int)$data['markup']) $errors['markup']='Fixed fare cannot be lower than the minimum fare ('.(int)$data['fare_min_price'].').';
                $data['amt_per']='amt';
            }
            if($data['rule_action']==='block'){
                if(empty($this->tokens($data['airline_code']))) $errors['airline_code']='A block rule needs at least one airline code (blocking every airline is not allowed).';
                $data['markup']=0;
                $data['amt_per']='amt';
            }
            if($data['rule_action']==='discount' && $data['amt_per']==='per' && (int)$data['markup']>50 && !$request->boolean('confirm_high_discount')){
                $errors['markup']='Discounts above 50% need the "I confirm a high discount" box ticked.';
            }
        }

        if($errors) throw ValidationException::withMessages($errors);
        return $data;
    }

    private function normaliseCodes(string $value): string
    {
        return implode(',',array_values(array_unique($this->tokens($value))));
    }

    private function tokens($value): array
    {
        return array_values(array_filter(array_map(
            fn($v)=>strtoupper(trim($v)),
            preg_split('/[\s,;|]+/',(string)$value) ?: []
        )));
    }

    private function findConflicts(Markup $rule, Collection $pool): Collection
    {
        return $pool->filter(function($other) use($rule){
            if((int)$other->id===(int)$rule->id) return false;
            if($other->is_active!=='Yes' || $rule->is_active!=='Yes') return false;

            if(!$this->datesOverlap($rule->from_date,$rule->to_date,$other->from_date,$other->to_date)) return false;
            if(!$this->flagOverlap($rule,$other,['is_ownsite','is_facecompare'])) return false;
            if(!$this->flagOverlap($rule,$other,['is_oneway','is_roundtrip','is_multicity'])) return false;
            if(!$this->flagOverlap($rule,$other,['is_economy','is_peconomy','is_business','is_fclass'])) return false;

            // Same route type is a definite overlap we can detect without changing/guessing airport-country mapping.
            if($rule->departure_type!==$other->departure_type || $rule->arrival_type!==$other->arrival_type) return false;
            if(!$this->tokenOverlap($rule->departure_code,$other->departure_code)) return false;
            if(!$this->tokenOverlap($rule->arrival_code,$other->arrival_code)) return false;

            $a=$this->tokens($rule->airline_code);
            $b=$this->tokens($other->airline_code);
            $airOverlap=empty($a)||empty($b)||!empty(array_intersect($a,$b));
            return $airOverlap;
        })->values();
    }

    private function flagOverlap($a,$b,array $fields): bool
    {
        foreach($fields as $f) if($a->{$f}==='Yes' && $b->{$f}==='Yes') return true;
        return false;
    }

    private function tokenOverlap($a,$b): bool
    {
        $aa=$this->tokens($a); $bb=$this->tokens($b);
        return !empty(array_intersect($aa,$bb));
    }

    private function datesOverlap($a1,$a2,$b1,$b2): bool
    {
        if(!$a1||!$a2||!$b1||!$b2) return true;
        return $a1<=$b2 && $b1<=$a2;
    }

    private function audit(Request $request,?int $markupId,string $action,string $summary,$before=null,$after=null): void
    {
        if(!Schema::hasTable('fare_rule_activity')) return;
        DB::table('fare_rule_activity')->insert([
            'markup_id'=>$markupId,
            'admin_id'=>(int)$request->session()->get('crm_admin_id') ?: null,
            'action'=>$action,
            'summary'=>$summary,
            'before_json'=>$before?json_encode($before,JSON_UNESCAPED_SLASHES):null,
            'after_json'=>$after?json_encode($after,JSON_UNESCAPED_SLASHES):null,
            'created_at'=>now(),
        ]);
    }

    private function historyRows(int $id,int $limit): Collection
    {
        if(!Schema::hasTable('fare_rule_activity')) return collect();
        return DB::table('fare_rule_activity')
            ->leftJoin('admins','fare_rule_activity.admin_id','=','admins.id')
            ->where('fare_rule_activity.markup_id',$id)
            ->select('fare_rule_activity.*','admins.name as admin_name')
            ->orderByDesc('fare_rule_activity.id')->limit($limit)->get();
    }
}
