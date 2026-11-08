<?php

declare(strict_types=1);

use LaravelBarion\LaravelBarion\LaravelBarion;

it('resolves the singleton', function () {
    expect(app(LaravelBarion::class))->toBeInstanceOf(LaravelBarion::class);
});

it('returns the same instance from the container', function () {
    expect(app(LaravelBarion::class))->toBe(app(LaravelBarion::class));
});

it('merges the package config', function () {
    expect(config('laravel-barion.placeholder'))->toBe('default');
});

it('loads the package views', function () {
    expect(view()->exists('laravel-barion::placeholder'))->toBeTrue();
});
