<div align="center">
    <h1>Laravel Barion</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/szabrob/laravel-barion"><img src="https://img.shields.io/packagist/v/szabrob/laravel-barion.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/szabrob/laravel-barion"><img src="https://img.shields.io/packagist/php-v/szabrob/laravel-barion.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/szabrob/laravel-barion"><img src="https://badge.laravel.cloud/badge/szabrob/laravel-barion?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/szabrob/laravel-barion/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/szabrob/laravel-barion/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/szabrob/laravel-barion"><img src="https://img.shields.io/packagist/dt/szabrob/laravel-barion.svg?style=flat-square" alt="Total Downloads"></a>
</p>

Barion Smart Gateway integration for Laravel with typed DTOs, callback events and Barion Pixel support.

## Installation

You can install the package via Composer:

```bash
composer require szabrob/laravel-barion
```

You may publish all of the package's resources at once:

```bash
php artisan vendor:publish --tag="laravel-barion"
```

Or, you may publish each resource individually:

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="laravel-barion-config"
```

### Publishing the Views

```bash
php artisan vendor:publish --tag="laravel-barion-views"
```

## Usage

<!-- Add a basic usage example here. -->

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Laravel Barion! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Szabó Róbert](https://github.com/szabrob)
- [All Contributors](../../contributors)

## License

Laravel Barion is open-sourced software licensed under the [MIT license](LICENSE.md).
