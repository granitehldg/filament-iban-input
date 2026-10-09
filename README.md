# Filament IBAN Input

[![Latest Version on Packagist](https://img.shields.io/packagist/v/granitehldg/filament-iban-input.svg)](https://packagist.org/packages/granitehldg/filament-iban-input)
[![Tests](https://img.shields.io/github/actions/workflow/status/granitehldg/filament-iban-input/run-tests.yml?label=tests)](https://github.com/granitehldg/filament-iban-input/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/granitehldg/filament-iban-input.svg)](https://packagist.org/packages/granitehldg/filament-iban-input)
[![License](https://img.shields.io/packagist/l/granitehldg/filament-iban-input.svg)](https://github.com/granitehldg/filament-iban-input/blob/main/LICENSE)

A [Filament](https://filamentphp.com) form field for IBAN (International Bank Account Number) input with real-time formatting, per-country length validation (ISO 13616), and mod-97 checksum verification.

## Features

- Real-time formatting with country code dropdown
- Per-country IBAN length validation (ISO 13616, ~100 countries)
- Mod-97 checksum validation (can be disabled)
- Server-side + client-side (Alpine.js) validation
- Dark mode support, LTR-safe, copy-to-clipboard
- Works with Filament v3 and v4

## Requirements

- PHP `^8.2` with `ext-bcmath`
- Laravel with Filament `^3.0 || ^4.0`

## Installation

```bash
composer require granitehldg/filament-iban-input
```

The service provider is auto-discovered. No manual registration needed.

Optionally publish the views:

```bash
php artisan vendor:publish --tag=filament-iban-input-views
```

## Usage

### Basic usage

```php
use Granite\FilamentIban\Forms\Components\IbanInput;

IbanInput::make('iban')
    ->label('Bank Account (IBAN)')
    ->required();
```

### Limit countries

```php
IbanInput::make('iban')
    ->countries(['DE', 'FR', 'NL', 'GB'])
    ->defaultCountry('DE')
    ->required();
```

### Accept all IBAN countries

By default only `['UA', 'EG', 'DE', 'GB', 'FR', 'NL']` are offered. To accept any known ISO 13616 country:

```php
IbanInput::make('iban')
    ->allCountries()
    ->required();
```

### Disable checksum validation

```php
IbanInput::make('iban')
    ->validateChecksum(false) // format/length only
    ->required();
```

### Custom lengths

```php
IbanInput::make('iban')
    ->countries(['ZZ'])
    ->countryLengths(['ZZ' => 6])
    ->validateChecksum(false);
```

The component stores the complete IBAN as a single string, e.g. `DE89370400440532013000`.

### Helpers

```php
use Granite\FilamentIban\Forms\Components\IbanInput;

IbanInput::validateIbanChecksum('DE89370400440532013000'); // true
IbanInput::normalize('de89 3704 0044 0532 0130 00'); // DE89370400440532013000
```

See [USAGE_EXAMPLE.md](USAGE_EXAMPLE.md) for migrations, models, and advanced validation examples.

## Validation

Server-side validation checks, in order:

1. Country code is 2 letters and (unless `allCountries()`) in the allowed list
2. Two numeric check digits after the country code
3. Body length matches the per-country ISO 13616 length
4. Mod-97 checksum equals `1` (unless disabled)

Empty values pass through so `->required()` / `->nullable()` behave as expected.

## Testing

```bash
composer install
vendor/bin/phpunit
```

Valid IBANs for manual testing:

- `DE89370400440532013000`
- `GB29NWBK60161331926819`
- `FR1420041010050500013M02606`
- `NL91ABNA0417164300`
- `UA213223130000026007233566001`

## Building assets

```bash
npm install
npm run build
```

This compiles `resources/css/index.css` to `resources/dist/filament-iban-input.css`. Commit the built file before tagging a release.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release notes. Follow [SemVer](https://semver.org): tagged releases (`v4.1.0`, ...) are what Packagist publishes.

## Contributing

Issues and PRs are welcome. Please run tests before submitting.

## Security

If you discover a security issue, please email the maintainers privately instead of opening a public issue.

## Credits

Developed by [Granite Holding](https://github.com/granitehldg).

## License

MIT. See [LICENSE](LICENSE).
