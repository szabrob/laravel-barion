# Release Notes

## [Unreleased](https://github.com/szabrob/laravel-barion/compare/v0.1.0...1.x)

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


## [v0.1.0](https://github.com/szabrob/laravel-barion/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
