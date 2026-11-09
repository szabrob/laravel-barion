# Release Notes

## [Unreleased](https://github.com/szabrob/laravel-barion/compare/v0.3.1...HEAD)

## [v0.3.1](https://github.com/szabrob/laravel-barion/compare/v0.3.0...v0.3.1) - 2026-11-09

### Added

- Per-IP callback rate limit (`callback.rate_limit_per_ip`, 1000 per minute by default) next to the per-IP-and-`paymentId` limit, so random well-formed payment ids can no longer trigger unlimited PaymentState calls.

### Added

- Per-IP callback rate limit (`callback.rate_limit_per_ip`, 1000 per minute by default) next to the per-IP-and-`paymentId` limit, so random well-formed payment ids can no longer trigger unlimited PaymentState calls.

## [v0.3.0](https://github.com/szabrob/laravel-barion/compare/v0.2.0...v0.3.0) - 2026-11-09

### Added

- Token payments are documented and tested end to end on the Barion test API. The README covers the three recurrence types (`OneClickPayment`, `MerchantInitiatedPayment`, `RecurringPayment`) and their rules, card registration, storing the `traceId`, scheduled charges, the off-site 3D Secure authentication with Barion's `barion.offsitegw` library, and the token error codes.
- `completePayment()` (v2 Payment/Complete) with `CompletePaymentRequestData` and `CompletePaymentResponseData`, to complete a `OneClickPayment` token charge after the off-site 3D Secure authentication. Payment/Complete needs the POS key in the request body too, and the package adds it.
- `PaymentPartiallySucceeded` callback event. A partially finished reservation used to dispatch no status event.

### Changed

- `BarionApiException::$body` and `BarionResponseException::$body` replace the `AuthData` and `POSKey` fields with `[redacted]`. Barion echoes the POS key in `AuthData` in every error response, so it used to end up in logs and error trackers.
- The callback rate limit is keyed by IP address and `paymentId` instead of IP address only, so busy shops no longer throttle Barion's callbacks.

### Maintenance

- Workbench: card registration on the start form, scheduled token charges (`barion:charge-due`, run by the scheduler every minute), and off-site 3D Secure authentication with `completePayment()` for `OneClickPayment` charges

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
