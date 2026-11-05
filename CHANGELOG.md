# Changelog

All notable changes to `granitehldg/filament-iban-input` are documented here. This project follows [Semantic Versioning](https://semver.org).

## [Unreleased]

## [v5.0.0] - 2026-11-05

### Added

- Filament v5 support (`filament/forms`, `filament/support` `^3.0 || ^4.0 || ^5.0`).
- CI matrix runs tests against Filament v3, v4 and v5, at both the lowest installable and the latest Filament versions.
- Test that renders `IbanInput` inside a Livewire form.

### Changed (breaking)

- Package renamed from `granitehldg/filament-iban` to `granitehldg/filament-iban-input`. Update your `composer.json` require.
- View namespace renamed from `filament-iban::` to `filament-iban-input::`. The publish tag is now `filament-iban-input-views` (was `filament-iban-views`). Move overridden views from `resources/views/vendor/filament-iban/` to `resources/views/vendor/filament-iban-input/`, or they are silently ignored.
- CSS asset renamed from `filament-iban` (package `granite/filament-iban`) to `filament-iban-input` (package `granitehldg/filament-iban-input`). Run `php artisan filament:assets` and delete the stale `public/css/granite/filament-iban/`.

## [v4.1.0] - 2026-05-06

### Added

- Support for all ISO 13616 IBAN countries with per-country body lengths.
- `allCountries()` mode to accept any known IBAN country code.
- `countryLengths()` override and `getCountryLengths()` / `getMaxDigitsForCountry()` helpers.
- Normalization is now case-insensitive for country configuration.

### Fixed

- Enforced LTR direction (`dir="ltr"`) on the field wrapper.
- Stabilized international IBAN validation (length + checksum).

## [v4.0.0] - 2026-04-23

### Added

- Filament v4 support alongside v3 (`filament/forms`, `filament/support` `^3.0 || ^4.0`).
- IBAN form field with country dropdown, formatting, and mod-97 checksum validation.

[Unreleased]: https://github.com/granitehldg/filament-iban-input/compare/v5.0.0...main
[v5.0.0]: https://github.com/granitehldg/filament-iban-input/compare/v4.1.0...v5.0.0
[v4.1.0]: https://github.com/granitehldg/filament-iban-input/compare/v4.0.0...v4.1.0
[v4.0.0]: https://github.com/granitehldg/filament-iban-input/releases/tag/v4.0.0
