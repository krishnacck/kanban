<?php

// Feature: kanban-board, Property 4: rate limiting blocks after 5 failures

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('P4: 6th failed login attempt is blocked with 429', function () {
    for ($i = 0; $i < 100; $i++) {
        RateLimiter::clear('login');

        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        // 5 failed attempts
        for ($j = 0; $j < 5; $j++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
        $this->assertGuest();

        // Clean up for next iteration
        $user->delete();
        RateLimiter::clear('login');
    }
});
