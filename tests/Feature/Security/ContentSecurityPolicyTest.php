<?php

declare(strict_types=1);

use Domain\User\Models\User;

test('content security policy headers are present on public pages', function () {
    $response = $this->get(route('home'));

    $response->assertStatus(200);
    $response->assertHeader('Content-Security-Policy');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

test('content security policy headers are present on authenticated pages', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertHeader('Content-Security-Policy');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'DENY');
});

test('csp allows stripe scripts', function () {
    $response = $this->get(route('home'));

    $csp = $response->headers->get('Content-Security-Policy');
    
    expect($csp)->toContain('https://js.stripe.com');
    expect($csp)->toContain('https://api.stripe.com');
});

test('csp restricts default sources to self', function () {
    $response = $this->get(route('home'));

    $csp = $response->headers->get('Content-Security-Policy');
    
    expect($csp)->toContain("default-src 'self'");
});

test('csp includes form-action restriction', function () {
    $response = $this->get(route('home'));

    $csp = $response->headers->get('Content-Security-Policy');
    
    expect($csp)->toContain("form-action 'self'");
});

