<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::orderBy('price_monthly')->get();

        return view('admin.billing.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.billing.plans.form', [
            'plan' => new SubscriptionPlan(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePlan($request);
        SubscriptionPlan::create($data);

        return redirect()->route('admin.billing.plans.index')->with('status', 'Plan created.');
    }

    public function edit(SubscriptionPlan $plan)
    {
        return view('admin.billing.plans.form', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $data = $this->validatePlan($request);
        $plan->update($data);

        return redirect()->route('admin.billing.plans.index')->with('status', 'Plan updated.');
    }

    private function validatePlan(Request $request): array
    {
        $planId = $request->route('plan')?->id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('subscription_plans', 'slug')->ignore($planId)],
            'description' => ['nullable', 'string'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:3'],
            'billing_interval' => ['required', 'string'],
            'ai_included_tokens_monthly' => ['required', 'integer', 'min:0'],
            'billing_mode' => ['required', 'in:postpaid,prepaid'],
            'allow_overage' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $data['allow_overage'] = $request->boolean('allow_overage');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
