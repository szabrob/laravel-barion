# Release Notes

## [Unreleased](https://github.com/szabrob/laravel-barion/compare/v0.2.0...HEAD)

### Added

- `PaymentPartiallySucceeded` callback event. A partially finished reservation used to dispatch no status event.
- `completePayment()` (v2 Payment/Complete) with `CompletePaymentRequestData` and `CompletePaymentResponseData`, to complete a 3D Secure authenticated token payment

### Changed

- The callback rate limit is keyed by IP address and `paymentId` instead of IP address only, so busy shops no longer throttle Barion's callbacks.

## [v0.2.0](https://github.com/szabrob/laravel-barion/compare/v0.1.0...v0.2.0) - 2026-11-08

### Breaking Changes

- The namespace changed from `LaravelBarion\LaravelBarion` to `Szabrob\LaravelBarion`, and the facade from `LaravelBarion` to `Barion`. See the [upgrade guide](https://github.com/szabrob/laravel-barion/blob/v0.2.0/UPGRADE.md).

### Added

- `PaymentReserved`, `PaymentAuthorized` and `PaymentRefunded` callback events
- `PaymentStateData::refundTransactions()` and `refundedAmount()`

### Maintenance

- CI: no fail-fast, retried dependency installation, bash shell on every OS

## [v0.1.0](https://github.com/szabrob/laravel-barion/releases/tag/v0.1.0) - 2026-11-08

### Added

- Barion Smart Gateway client with spatie/laravel-data request and response DTOs
- Payment/Start (v2), PaymentState (v4), Refund, FinishReservation, Capture and CancelAuthorization
- POS key authentication via the `x-pos-key` header, with separate test and production configuration
- Automatic, deterministic `Idempotency-Key` header on every POST call, with retries for connection errors and 5xx responses
- Callback route that verifies the payment through the PaymentState API and dispatches `PaymentStateReceived`, `PaymentSucceeded` and `PaymentFailed` events
- `<x-laravel-barion::pixel />` Barion Pixel Blade component
- Per-IP rate limit on the callback route (`callback.rate_limit`); unknown payment ids answer 404 without logging an error
- `BarionResponseException` for responses that cannot be mapped to the expected DTO
- Money fields are rounded to 2 decimals before sending
