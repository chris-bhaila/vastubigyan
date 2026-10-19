<?php

namespace Tests\Feature;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class WelcomeMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_welcome_mail_sends_immediately_without_a_queue_worker(): void
    {
        $user = User::factory()->create();

        $this->assertNotInstanceOf(ShouldQueue::class, new WelcomeMail($user));
    }

    public function test_the_welcome_mail_renders_the_users_name_logos_and_dashboard_link(): void
    {
        $user = User::factory()->create(['name' => 'Asha Shrestha']);

        $mail = new WelcomeMail($user);

        $mail->assertSeeInHtml('Namaste, Asha Shrestha!');
        $mail->assertSeeInHtml(asset('images/logo.png'), escape: false);
        $mail->assertSeeInHtml(asset('images/logo-mark.png'), escape: false);
        $mail->assertSeeInHtml(route('dashboard'));
        $mail->assertSeeInText('Namaste, Asha Shrestha!');
        $mail->assertSeeInText(route('dashboard'));
    }

    public function test_signing_up_via_the_api_sends_a_welcome_mail(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'password' => 'password123',
        ]);

        $response->assertCreated();

        $user = User::where('email', 'new-user@example.com')->firstOrFail();

        Mail::assertSent(WelcomeMail::class, fn (WelcomeMail $mail) => $mail->user->is($user));
    }

    public function test_a_first_time_google_sign_in_sends_a_welcome_mail(): void
    {
        Mail::fake();

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-123',
            'name' => 'Google User',
            'email' => 'google-user@example.com',
        ]));

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect();

        $user = User::where('email', 'google-user@example.com')->firstOrFail();

        Mail::assertSent(WelcomeMail::class, fn (WelcomeMail $mail) => $mail->user->is($user));
    }

    public function test_a_returning_google_sign_in_does_not_resend_the_welcome_mail(): void
    {
        $user = User::factory()->create([
            'email' => 'returning-user@example.com',
            'google_id' => 'google-456',
        ]);

        Mail::fake();

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-456',
            'name' => $user->name,
            'email' => $user->email,
        ]));

        $this->get(route('auth.google.callback'))->assertRedirect();

        Mail::assertNotSent(WelcomeMail::class);
    }
}
