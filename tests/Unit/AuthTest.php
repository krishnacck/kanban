<?php

// Feature: kanban-board, Property 1: stored hash is bcrypt, not plaintext

use App\Models\User;
use Illuminate\Support\Facades\Hash;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('P1: stores bcrypt hash not plaintext for any password', function () {
    // Feature: kanban-board, Property 1: password hashing
    for ($i = 0; $i < 100; $i++) {
        $password = fake()->password(8, 32);
        $user = User::factory()->create(['password' => Hash::make($password)]);

        expect($user->password)->not->toBe($password);
        expect($user->password)->toStartWith('$2y$');
        expect(Hash::check($password, $user->password))->toBeTrue();
    }
});
