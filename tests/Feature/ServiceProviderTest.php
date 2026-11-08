<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Szabrob\LaravelBarion\Facades\Barion;
use Szabrob\LaravelBarion\LaravelBarion;
use Szabrob\LaravelBarion\LaravelBarionServiceProvider;

it('resolves the client as a singleton', function (): void {
    expect(app(LaravelBarion::class))
        ->toBeInstanceOf(LaravelBarion::class)
        ->toBe(app(LaravelBarion::class))
        ->toBe(Barion::getFacadeRoot());
});

it('merges the package config with test environment defaults', function (): void {
    expect(require __DIR__.'/../../config/laravel-barion.php')
        ->environment->toBe('test')
        ->callback->toMatchArray(['enabled' => true, 'path' => 'barion/callback'])
        ->and(config('laravel-barion.callback.path'))->toBe('barion/callback');
});

it('registers the publish tags', function (): void {
    $config = ServiceProvider::pathsToPublish(LaravelBarionServiceProvider::class, 'laravel-barion-config');
    $views = ServiceProvider::pathsToPublish(LaravelBarionServiceProvider::class, 'laravel-barion-views');

    expect($config)->toHaveCount(1)
        ->and(array_values($config)[0])->toBe(config_path('laravel-barion.php'))
        ->and(array_values($views)[0])->toBe(resource_path('views/vendor/laravel-barion'))
        ->and(ServiceProvider::pathsToPublish(LaravelBarionServiceProvider::class, 'laravel-barion'))->toHaveCount(2);
});

it('renders the Barion Pixel with the configured id', function (): void {
    config()->set('laravel-barion.pixel.id', 'BP-0123456789-01');

    $html = Blade::render('<x-laravel-barion::pixel nonce="abc" />');

    expect($html)
        ->toContain('https://pixel.barion.com/bp.js')
        ->toContain('nonce="abc"')
        ->toContain("bp('init', 'addBarionPixelId'")
        ->toContain("'BP-0123456789-01'")
        ->toContain('ba_pixel_id=BP-0123456789-01');
});

it('renders nothing without a Barion Pixel id', function (): void {
    config()->set('laravel-barion.pixel.id', null);

    expect(trim(Blade::render('<x-laravel-barion::pixel />')))->toBe('');
});

it('escapes the Barion Pixel id', function (): void {
    $html = Blade::render('<x-laravel-barion::pixel :id="$id" />', ['id' => "BP-1'</script><script>alert(1)"]);

    expect($html)->not->toContain('</script><script>alert(1)');
});
