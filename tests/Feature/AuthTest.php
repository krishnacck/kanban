<?php

// Feature: kanban-board, Property 2: invalid credentials never establish session

use App\Models\User;
use Illuminate\Support\Facades\Hash;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('login happy path establishes session and redirects to board', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect('/board');
    $this->assertAuthenticatedAs($user);
});

it('registration happy path creates user and redirects to board', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/board');
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com', 'role' => 'user']);
});

it('logout invalidates session and redirects to login', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/logout');

    $response->assertRedirect('/login');
    $this->assertGuest();
});

it('P2: invalid credentials never establish session', function () {
    for ($i = 0; $i < 100; $i++) {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        // Wrong password
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password-' . $i,
        ]);

        $this->assertGuest();

        // Non-existent email
        $response = $this->post('/login', [
            'email' => 'nonexistent' . $i . '@example.com',
            'password' => 'any-password',
        ]);

        $this->assertGuest();
    }
});
