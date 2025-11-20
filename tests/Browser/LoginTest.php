<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    public function test_admin_can_log_in_from_login_page(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );

        $this->browse(function (Browser $browser) use ($user): void {
            $browser
                // baseUrl() in DuskTestCase + relative path:
                ->visit('/login')

                // wait for the form to be ready
                ->waitFor('#email', 5)

                // use the real IDs from your HTML
                ->type('#email', $user->email)
                ->type('#password', 'password')

                // exact visible text on the button
                ->press('LOG IN')

                // after successful login, you expect redirect to /dashboard
                ->waitForLocation('/dashboard', 10)
                ->assertPathIs('/dashboard');
        });
    }
}
