<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            // ✅ Free tier – small prepaid hard cap, no overage
            [
                'name'                     => 'JobSeeker Free',
                'slug'                     => 'jobseeker-free',
                'description'              => 'Free plan with a small monthly AI token allowance. Great for trying out JobGenie.ai.',
                'price_monthly'            => 0,
                'currency'                 => 'USD',
                'billing_interval'         => 'monthly',
                'billing_mode'             => 'prepaid',        // hard cap
                'ai_included_tokens_monthly' => 20_000,         // 20k tokens
                'allow_overage'            => false,            // no overage on prepaid
                'is_active'                => true,
                'metadata'                 => [
                    'label'       => 'Starter',
                    'highlight'   => 'Try before you commit',
                    'recommended' => false,
                ],
            ],

            // ✅ The one from your UI: JobSeeker Light – prepaid (hard cap)
            [
                'name'                     => 'JobSeeker Light',
                'slug'                     => 'jobseeker-light',
                'description'              => 'Affordable prepaid plan for active job seekers. Fixed monthly tokens, hard limit, no surprise bills.',
                'price_monthly'            => 20,
                'currency'                 => 'USD',
                'billing_interval'         => 'monthly',
                'billing_mode'             => 'prepaid',        // Prepaid (hard cap)
                'ai_included_tokens_monthly' => 100_000,        // adjust as you like
                'allow_overage'            => false,            // hard cap – no overage billing
                'is_active'                => true,
                'metadata'                 => [
                    'label'       => 'Most Popular (Prepaid)',
                    'highlight'   => 'Prepaid token bundle, hard cap',
                    'recommended' => true,
                ],
            ],

            // ✅ Pro – postpaid with overage
            [
                'name'                     => 'JobSeeker Pro',
                'slug'                     => 'jobseeker-pro',
                'description'              => 'For power users running lots of applications. Generous included tokens plus postpaid overage.',
                'price_monthly'            => 39,
                'currency'                 => 'USD',
                'billing_interval'         => 'monthly',
                'billing_mode'             => 'postpaid',       // allows overage
                'ai_included_tokens_monthly' => 300_000,
                'allow_overage'            => true,
                'is_active'                => true,
                'metadata'                 => [
                    'label'       => 'Pro',
                    'highlight'   => 'Best for active applications',
                    'recommended' => false,
                ],
            ],

            // ✅ Team – postpaid, meant for agencies / bootcamps
            [
                'name'                     => 'JobGenie Team',
                'slug'                     => 'jobgenie-team',
                'description'              => 'Team / agency plan with higher limits and shared usage across multiple recruiters or mentors.',
                'price_monthly'            => 99,
                'currency'                 => 'USD',
                'billing_interval'         => 'monthly',
                'billing_mode'             => 'postpaid',
                'ai_included_tokens_monthly' => 1_000_000,
                'allow_overage'            => true,
                'is_active'                => true,
                'metadata'                 => [
                    'label'       => 'Team',
                    'highlight'   => 'Shared workspace for teams',
                    'recommended' => false,
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
