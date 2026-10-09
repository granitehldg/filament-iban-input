# Changelog

All notable changes to `granitehldg/filament-iban-input` are documented here. This project follows [Semantic Versioning](https://semver.org).

## [Unreleased]

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

[Unreleased]: https://github.com/granitehldg/filament-iban-input/compare/v4.1.0...main
[v4.1.0]: https://github.com/granitehldg/filament-iban-input/compare/v4.0.0...v4.1.0
[v4.0.0]: https://github.com/granitehldg/filament-iban-input/releases/tag/v4.0.0
