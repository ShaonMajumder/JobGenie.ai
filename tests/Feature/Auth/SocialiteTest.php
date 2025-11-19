<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class SocialiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_route_uses_socialite_driver(): void
    {
        Config::set('services.google.redirect', 'http://localhost/auth/google/callback');

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('redirectUrl')->once()->with('http://localhost/auth/google/callback')->andReturnSelf();
        $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/o/oauth2/v2/auth'));

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $response = $this->get(route('oauth.google.redirect'));

        $response->assertRedirect('https://accounts.google.com/o/oauth2/v2/auth');
    }

    public function test_google_callback_creates_user_and_logs_in(): void
    {
        Config::set('services.google.redirect', null);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-123');
        $socialiteUser->shouldReceive('getEmail')->andReturn('oauth@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('OAuth Tester');
        $socialiteUser->shouldReceive('getNickname')->andReturn('oauth');

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $response = $this->get(route('oauth.google.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'oauth@example.com',
            'google_id' => 'google-123',
        ]);
    }
}
