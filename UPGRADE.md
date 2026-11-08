# Upgrade Guide

## Upgrading to 0.2 from 0.1

### Namespace

The package namespace changed from `LaravelBarion\LaravelBarion` to `Szabrob\LaravelBarion`, to match the package name. Update every import:

```diff
-use LaravelBarion\LaravelBarion\Data\Requests\PaymentRequestData;
+use Szabrob\LaravelBarion\Data\Requests\PaymentRequestData;
```

Find and replace `LaravelBarion\LaravelBarion\` with `Szabrob\LaravelBarion\` in your application. If you registered the service provider by hand (for example in `bootstrap/providers.php`), update it too. Package auto-discovery picks up the new name after `composer update`.

### Facade

The facade was renamed from `LaravelBarion` to `Barion`, and its auto-alias is now `Barion`:

```diff
-use LaravelBarion\LaravelBarion\Facades\LaravelBarion;
+use Szabrob\LaravelBarion\Facades\Barion;

-LaravelBarion::startPayment($request);
+Barion::startPayment($request);
```

The client class keeps its name (`Szabrob\LaravelBarion\LaravelBarion`), so code that injects it only needs the new namespace.
