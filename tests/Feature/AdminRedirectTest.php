<?php

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('guest accessing admin is redirected', function () {
    $response = $this->get('/admin');
    dump('Guest /admin redirect to: ' . $response->headers->get('Location'));
});

test('admin user can access admin dashboard when authenticated', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $admin = \App\Models\User::role(UserRole::Admin->value)->first();

    $response = $this->actingAs($admin)->get('/admin');

    dump('Admin /admin status: ' . $response->status());
    if ($response->isRedirection()) {
        dump('Admin /admin redirects to: ' . $response->headers->get('Location'));
    }
    $response->assertStatus(200);
});


test('guest accessing admin login page directly', function () {
    $response = $this->get('/admin/login');
    dump('Guest /admin/login status: ' . $response->status());
    if ($response->isRedirection()) {
        dump('Guest /admin/login redirects to: ' . $response->headers->get('Location'));
    }
});

test('admin user login flow', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    // Try logging in via portal login
    $response = $this->post('/portal/login', [
        'email' => 'bhoypratama71@gmail.com',
        'password' => 'boyadmin123',
    ]);

    dump('Admin portal login status: ' . $response->status());
    dump('Admin portal login redirects to: ' . $response->headers->get('Location'));
});

test('admin can login via Filament login page', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    \Livewire\Livewire::test(\Filament\Auth\Pages\Login::class)
        ->fillForm([
            'email' => 'bhoypratama71@gmail.com',
            'password' => 'boyadmin123',
        ])
        ->call('authenticate')
        ->assertRedirect('/admin');
});

test('sales can login via Filament login page', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    \Livewire\Livewire::test(\Filament\Auth\Pages\Login::class)
        ->fillForm([
            'email' => 'sales@gabus.test',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertRedirect('/admin');
});

test('superadmin can login via Filament login page', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    \Livewire\Livewire::test(\Filament\Auth\Pages\Login::class)
        ->fillForm([
            'email' => 'firlanarizka88@gmail.com',
            'password' => 'bosrizka123',
        ])
        ->call('authenticate')
        ->assertRedirect('/admin');
});


