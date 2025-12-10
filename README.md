# Filament IBAN Input Plugin

A Filament v3 plugin that provides a beautiful IBAN (International Bank Account Number) input field with real-time formatting, validation, and checksum verification.

## Features

- **Real-time Formatting**: Automatically formats IBAN digits as user types (2-4-4-17 pattern)
- **Country Code Selection**: Dropdown for selecting the country code prefix
- **Checksum Validation**: Validates IBAN using modulo 97 algorithm
- **Visual Feedback**: Real-time status indicators (valid, invalid, incomplete)
- **Dark Mode Support**: Fully compatible with Filament's dark mode
- **Copy to Clipboard**: Built-in copy functionality for the complete IBAN
- **Customizable**: Configure available countries, default country, and validation rules

## Installation

The package is already installed in this project as a local path repository.

To use it in other projects:

```bash
composer require granitehldg/filament-iban
```

## Usage

### Basic Usage

```php
use Granite\FilamentIban\Forms\Components\IbanInput;

IbanInput::make('iban')
    ->label('Bank Account (IBAN)')
    ->required()
```

### With Custom Countries

```php
IbanInput::make('iban')
    ->label('Bank Account (IBAN)')
    ->countries(['DE', 'FR', 'NL', 'GB'])
    ->defaultCountry('DE')
    ->required()
```

### Disable Checksum Validation

```php
IbanInput::make('iban')
    ->label('Bank Account (IBAN)')
    ->validateChecksum(false) // Only validates format, not checksum
    ->required()
```

### Custom Digit Length

```php
IbanInput::make('iban')
    ->label('Bank Account (IBAN)')
    ->maxDigits(27) // Default is 27
    ->required()
```

### Show Full IBAN Display

To display the full IBAN with copy functionality, add helper text:

```php
IbanInput::make('iban')
    ->label('Bank Account (IBAN)')
    ->helperText('The complete IBAN will be displayed below')
    ->required()
```

## Validation

The component automatically validates:

1. **Country Code**: Must be one of the allowed countries
2. **Length**: Must contain exactly the specified number of digits (default: 27)
3. **Checksum**: Validates using the modulo 97 algorithm (can be disabled)

### Server-Side Validation

The validation happens both client-side (for immediate feedback) and server-side (for security).

### Custom Validation

You can add additional validation rules:

```php
IbanInput::make('iban')
    ->label('Bank Account (IBAN)')
    ->rule('unique:bank_accounts,iban')
    ->required()
```

## Data Format

The component stores the complete IBAN as a single string with the country code:

```
UA380019000500000026318000212
```

- First 2 characters: Country code (e.g., 'UA')
- Remaining characters: Formatted digits

## Styling

The component uses Filament's design system and automatically adapts to:
- Light/Dark mode
- Form field states (error, disabled, focused)
- Responsive layouts

## Development

### Building Assets

To rebuild the CSS after making changes:

```bash
cd packages/filament-iban
npm install
npm run build
```

### Testing

The IBAN validation uses the standard modulo 97 algorithm as defined in ISO 13616.

Example valid IBANs for testing:
- UA380019000500000026318000212
- DE89370400440532013000
- GB82WEST12345698765432

## Technical Details

### IBAN Validation Algorithm

The checksum validation follows the ISO 13616 standard:

1. Move the first 4 characters to the end
2. Convert letters to numbers (A=10, B=11, ..., Z=35)
3. Calculate modulo 97 of the resulting number
4. Valid if remainder equals 1

### Components

- **IbanInput.php**: Main form field class (`packages/filament-iban/src/Forms/Components/IbanInput.php:1`)
- **iban-input.blade.php**: Blade view with Alpine.js component
- **FilamentIbanServiceProvider.php**: Service provider for asset registration

## Browser Support

Works in all modern browsers that support:
- Alpine.js
- CSS Grid
- ES6 JavaScript

## License

MIT

## Credits

Developed by Granite Holding
