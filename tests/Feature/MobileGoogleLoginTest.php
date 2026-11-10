<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\MobileGoogleLogin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class MobileGoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        Mail::fake();
    }

    private function pending(string $state): array
    {
        return ['mobile_google_pending' => [
            'state' => $state, 'expires_at' => now()->addMinutes(10)->timestamp,
        ]];
    }

    public function test_prepare_creates_session_bound_state_and_signed_launch(): void
    {
        $response = $this->get('/auth/mobile/google?state='.str_repeat('a', 64))
            ->assertOk()->assertViewIs('auth.mobile-google-prepare')
            ->assertHeader('Cache-Control', 'no-store, private');
        $state = session('mobile_google_pending.state');
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $state);
        $this->assertNotSame(str_repeat('a', 64), $state);
        $response->assertViewHas('launch', fn ($url) => str_contains($url, 'signature='));
    }

    public function test_launch_rejects_unsigned_or_expired_requests(): void
    {
        $state = bin2hex(random_bytes(32));
        $this->get('/auth/mobile/google/launch?state='.$state)->assertForbidden();
        $expired = URL::temporarySignedRoute('auth.mobile.google.launch', now()->subMinute(), compact('state'));
        $this->get($expired)->assertForbidden();
    }

    public function test_signed_launch_redirects_to_google_in_browser_session(): void
    {
        $state = bin2hex(random_bytes(32));
        $driver = Mockery::mock();
        $driver->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/test'));
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($driver);
        $url = URL::temporarySignedRoute('auth.mobile.google.launch', now()->addMinutes(10), compact('state'));
        $this->get($url)->assertRedirect('https://accounts.google.com/test')
            ->assertSessionHas('mobile_google_browser_state', $state);
    }

    public function test_code_is_hashed_and_can_only_be_used_once_in_original_session(): void
    {
        $user = User::factory()->create();
        $state = bin2hex(random_bytes(32));
        $code = MobileGoogleLogin::issue($user, $state);
        $this->assertDatabaseHas('mobile_google_logins', [
            'code_hash' => hash('sha256', $code), 'state_hash' => hash('sha256', $state),
        ]);
        $this->post('/auth/mobile/google/complete', compact('code', 'state'))->assertStatus(419);
        $this->assertGuest();
        $this->withSession($this->pending(str_repeat('b', 64)))
            ->post('/auth/mobile/google/complete', compact('code', 'state'))->assertStatus(419);
        $this->withSession($this->pending($state))
            ->post('/auth/mobile/google/complete', compact('code', 'state'))->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
        $this->assertNull(MobileGoogleLogin::redeem($code, $state));
        $this->assertDatabaseCount('mobile_google_logins', 0);
    }

    public function test_expired_code_and_expired_session_are_rejected(): void
    {
        $state = bin2hex(random_bytes(32));
        $code = MobileGoogleLogin::issue(User::factory()->create(), $state);
        $this->travel(61)->seconds();
        $this->withSession($this->pending($state))
            ->post('/auth/mobile/google/complete', compact('code', 'state'))->assertStatus(419);
        $this->assertGuest();
        $this->withSession(['mobile_google_pending' => ['state' => $state, 'expires_at' => now()->subSecond()->timestamp]])
            ->get('/auth/mobile/google/return')->assertStatus(419);
    }

    public function test_mobile_callback_issues_code_without_logging_browser_in(): void
    {
        $user = User::factory()->create(['google_id' => 'google-test-id']);
        $state = bin2hex(random_bytes(32));
        $google = (new GoogleUser)->map(['id' => 'google-test-id', 'email' => $user->email, 'name' => $user->name]);
        $driver = Mockery::mock();
        $driver->shouldReceive('user')->once()->andReturn($google);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($driver);
        $this->withSession(['mobile_google_browser_state' => $state])
            ->get('/auth/google/callback')->assertOk()->assertViewIs('auth.mobile-google-finish')
            ->assertViewHas('state', $state)->assertSee('placko://auth/google', false);
        $this->assertGuest();
        $this->assertDatabaseCount('mobile_google_logins', 1);
    }

    public function test_cancelled_mobile_callback_shows_return_link_without_issuing_code(): void
    {
        $driver = Mockery::mock();
        $driver->shouldReceive('user')->once()->andThrow(new \RuntimeException('cancelled'));
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($driver);
        $this->withSession(['mobile_google_browser_state' => bin2hex(random_bytes(32))])
            ->get('/auth/google/callback?error=access_denied')->assertOk()
            ->assertViewHas('code', null)->assertSee('placko://auth/google?error=cancelled', false);
        $this->assertGuest();
        $this->assertDatabaseCount('mobile_google_logins', 0);
    }

    public function test_regular_web_google_login_still_logs_in_and_redirects_to_admin(): void
    {
        $user = User::factory()->create(['google_id' => 'web-google-id']);
        $google = (new GoogleUser)->map(['id' => 'web-google-id', 'email' => $user->email, 'name' => $user->name]);
        $driver = Mockery::mock();
        $driver->shouldReceive('user')->once()->andReturn($google);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($driver);
        $this->get('/auth/google/callback')->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('mobile_google_logins', 0);
    }
}
