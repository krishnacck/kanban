<?php

// Feature: kanban-board, Property 3: Google OAuth accounts get role=user

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function makeSocialiteUser(string $email, string $name, string $googleId): SocialiteUser
{
    $socialiteUser = new SocialiteUser();
    $socialiteUser->map([
        'id' => $googleId,
        'name' => $name,
        'email' => $email,
        'avatar' => 'https://example.com/avatar.jpg',
    ]);
    return $socialiteUser;
}

it('Google OAuth creates new user with role=user', function () {
    $socialiteUser = makeSocialiteUser('newgoogle@example.com', 'Google User', 'google-id-123');

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect('/board');
    $this->assertDatabaseHas('users', [
        'email' => 'newgoogle@example.com',
        'role' => 'user',
        'google_id' => 'google-id-123',
    ]);
    $this->assertAuthenticated();
});

it('Google OAuth links existing user by email', function () {
    $existing = User::factory()->create(['email' => 'existing@example.com', 'google_id' => null]);
    $socialiteUser = makeSocialiteUser('existing@example.com', 'Existing User', 'google-id-456');

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect('/board');
    $existing->refresh();
    expect($existing->google_id)->toBe('google-id-456');
    $this->assertAuthenticatedAs($existing);
});

it('Google OAuth error redirects to login with error', function () {
    Socialite::shouldReceive('driver->user')->andThrow(new \Exception('OAuth error'));

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect('/login');
    $this->assertGuest();
});

it('P3: all Google OAuth created accounts have role=user', function () {
    for ($i = 0; $i < 100; $i++) {
        $email = fake()->unique()->safeEmail();
        $socialiteUser = makeSocialiteUser($email, fake()->name(), 'google-' . $i);

        Socialite::shouldReceive('driver->user')->once()->andReturn($socialiteUser);

        $this->get('/auth/google/callback');

        $user = User::where('email', $email)->first();
        expect($user)->not->toBeNull();
        expect($user->role)->toBe('user');
    }
});
