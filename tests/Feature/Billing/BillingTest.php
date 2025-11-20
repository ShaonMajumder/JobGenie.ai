<?php

namespace Tests\Feature\Billing;

use App\Mail\InvoiceGeneratedMail;
use App\Mail\OutOfTokensMail;
use App\Models\AiUsageRecord;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Billing\AiUsageBillingService;
use App\Services\Billing\KillBillClient;
use App\Services\Billing\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->mockKillBill();
        $this->mockStripe();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_admin_can_create_prepaid_plan(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);

        $payload = [
            'name' => 'Starter Tokens',
            'slug' => 'starter-tokens',
            'description' => 'Prepaid hard-cap plan',
            'price_monthly' => 19,
            'currency' => 'USD',
            'billing_interval' => 'monthly',
            'ai_included_tokens_monthly' => 20000,
            'billing_mode' => 'prepaid',
            'allow_overage' => false,
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)->post(route('admin.billing.plans.store'), $payload);

        $response->assertRedirect(route('admin.billing.plans.index'));
        $this->assertDatabaseHas('subscription_plans', [
            'slug' => 'starter-tokens',
            'billing_mode' => 'prepaid',
            'allow_overage' => false,
        ]);
    }

    public function test_prepaid_user_is_blocked_when_tokens_exhausted(): void
    {
        $plan = SubscriptionPlan::factory()
            ->prepaid()
            ->create([
                'ai_included_tokens_monthly' => 100,
                'allow_overage' => false,
            ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->create([
            'current_period_start' => now()->startOfMonth(),
            'current_period_end' => now()->endOfMonth(),
        ]);

        Job::factory()->for($user)->create();
        $job = $user->jobs()->first();

        $usage = AiUsageRecord::factory()->create([
            'user_id' => $user->id,
            'total_tokens' => 100,
            'input_tokens' => 50,
            'output_tokens' => 50,
        ]);
        $usage->timestamps = false;
        $usage->forceFill(['created_at' => now()->startOfMonth()->addDay()])->save();

        $response = $this->actingAs($user)->post(route('jobs.sessions.store', $job), [
            'currency' => 'USD',
        ]);

        $response->assertRedirect(route('billing.index'));
        $response->assertSessionHas('error');
        Mail::assertSent(OutOfTokensMail::class);
        $this->assertSame($subscription->fresh()->id, $user->activeSubscription()->id);
    }

    public function test_postpaid_usage_generates_invoice_with_overage(): void
    {
        $plan = SubscriptionPlan::factory()->create([
            'billing_mode' => 'postpaid',
            'ai_included_tokens_monthly' => 100,
            'allow_overage' => true,
            'price_monthly' => 49,
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->create([
            'current_period_start' => now()->subMonth()->startOfMonth(),
            'current_period_end' => now()->subMonth()->endOfMonth(),
        ]);

        $usage = AiUsageRecord::factory()->create([
            'user_id' => $user->id,
            'total_tokens' => 150,
            'input_tokens' => 150,
            'output_tokens' => 0,
            'cost_total' => 1.50,
        ]);
        $usage->timestamps = false;
        $usage->forceFill(['created_at' => now()->subMonth()->startOfMonth()->addDay()])->save();

        Artisan::call('billing:generate-monthly-invoices');

        $invoice = Invoice::where('user_id', $user->id)->latest()->first();

        $this->assertNotNull($invoice);
        $this->assertEquals(49.0, (float) $invoice->amount_subscription);
        $this->assertEqualsWithDelta(0.5, (float) $invoice->amount_ai_usage, 0.01);
        $this->assertEqualsWithDelta(49.5, (float) $invoice->amount_total, 0.01);
        Mail::assertSent(InvoiceGeneratedMail::class);
    }

    public function test_remaining_tokens_helper_matches_usage(): void
    {
        $plan = SubscriptionPlan::factory()->prepaid()->create([
            'ai_included_tokens_monthly' => 1000,
            'allow_overage' => false,
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        Subscription::factory()->for($user)->for($plan, 'plan')->create([
            'current_period_start' => now()->startOfMonth(),
            'current_period_end' => now()->endOfMonth(),
        ]);

        $usage = AiUsageRecord::factory()->create([
            'user_id' => $user->id,
            'total_tokens' => 200,
        ]);
        $usage->timestamps = false;
        $usage->forceFill(['created_at' => now()->startOfMonth()->addDay()])->save();

        $this->assertSame(800, $user->remainingTokensForCurrentPeriod());
    }

    public function test_ai_usage_pricing_math_uses_configured_rates(): void
    {
        config()->set('ai_pricing.providers.gemini.models.gemini-2.5-flash.input_per_1k', 0.15);
        config()->set('ai_pricing.providers.gemini.models.gemini-2.5-flash.output_per_1k', 0.15);
        $service = app(AiUsageBillingService::class);

        $costs = $service->calculateCosts('gemini', 'gemini-2.5-flash', 1000, 0);

        $this->assertEqualsWithDelta(0.15, $costs['cost_input'], 0.001);
        $this->assertEquals('USD', $costs['currency']);
    }

    private function mockKillBill(): void
    {
        $mock = Mockery::mock(KillBillClient::class);
        $mock->shouldReceive('createAccount')->andReturn('kb-account');
        $mock->shouldReceive('createSubscription')->andReturn('kb-sub');
        $mock->shouldReceive('createInvoiceForSubscription')->andReturnNull();
        $mock->shouldReceive('markInvoiceAsPaid')->andReturnNull();

        $this->app->instance(KillBillClient::class, $mock);
    }

    private function mockStripe(): void
    {
        $mock = Mockery::mock(StripePaymentService::class);
        $mock->shouldReceive('createPaymentIntent')->andReturn([
            'payment_intent_id' => 'pi_test',
            'client_secret' => 'secret_test',
        ]);
        $mock->shouldReceive('confirmPaymentIntent')->andReturn((object) [
            'id' => 'pi_test',
            'status' => 'succeeded',
            'amount' => 999999,
        ]);

        $this->app->instance(StripePaymentService::class, $mock);
    }
}
