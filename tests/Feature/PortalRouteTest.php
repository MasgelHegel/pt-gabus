<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('portal catalog page loads successfully for guests', function () {
    $response = $this->get('/portal');
    $response->assertStatus(200);
});

test('login route redirects guests to portal login', function () {
    $response = $this->get('/login');
    $response->assertRedirect('/portal/login');
});
