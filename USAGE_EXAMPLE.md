# IBAN Input Usage Examples

## Basic Implementation

### In a Filament Form Resource

```php
<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Granite\FilamentIban\Forms\Components\IbanInput;

class BankAccountResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('account_holder')
                    ->required()
                    ->maxLength(255),

                IbanInput::make('iban')
                    ->label('International Bank Account Number')
                    ->required()
                    ->helperText('Enter the IBAN with country code'),

                Forms\Components\TextInput::make('swift_code')
                    ->maxLength(11)
                    ->label('SWIFT/BIC Code'),
            ]);
    }
}
```

## Advanced Configurations

### European Banks Only

```php
IbanInput::make('iban')
    ->label('EU Bank Account')
    ->countries(['DE', 'FR', 'IT', 'ES', 'NL', 'BE'])
    ->defaultCountry('DE')
    ->required()
```

### Custom Validation

```php
IbanInput::make('iban')
    ->label('Primary Bank Account')
    ->required()
    ->unique('bank_accounts', 'iban', ignoreRecord: true)
    ->helperText('This IBAN must be unique in our system')
```

### Format-Only Validation (No Checksum)

Useful for testing or when you want to accept IBANs that might not pass checksum validation:

```php
IbanInput::make('iban')
    ->label('Bank Account (IBAN)')
    ->validateChecksum(false)
    ->required()
```

### With Full IBAN Display

```php
IbanInput::make('iban')
    ->label('Bank Account')
    ->helperText('The complete IBAN will be shown below with copy functionality')
    ->required()
```

## In Filament Forms (Standalone)

```php
use Filament\Forms\Components\Form;
use Granite\FilamentIban\Forms\Components\IbanInput;

public function form(Form $form): Form
{
    return $form
        ->schema([
            IbanInput::make('recipient_iban')
                ->label('Recipient IBAN')
                ->countries(['UA', 'PL', 'RO', 'HU'])
                ->defaultCountry('UA')
                ->required(),
        ]);
}
```

## Database Schema

### Migration Example

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_holder');
            $table->string('iban', 34)->unique(); // Max IBAN length is 34 chars
            $table->string('swift_code', 11)->nullable();
            $table->string('bank_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
```

## Model Example

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Granite\FilamentIban\Forms\Components\IbanInput;

class BankAccount extends Model
{
    protected $fillable = [
        'account_holder',
        'iban',
        'swift_code',
        'bank_name',
    ];

    /**
     * Get the country code from the IBAN
     */
    public function getCountryCodeAttribute(): string
    {
        return substr($this->iban, 0, 2);
    }

    /**
     * Get formatted IBAN for display
     */
    public function getFormattedIbanAttribute(): string
    {
        $iban = $this->iban;
        return chunk_split($iban, 4, ' ');
    }

    /**
     * Validate the IBAN checksum
     */
    public function hasValidChecksum(): bool
    {
        return IbanInput::validateIbanChecksum($this->iban);
    }
}
```

## Custom Validation Rules

### Validate Against Specific Country

```php
use Granite\FilamentIban\Forms\Components\IbanInput;

IbanInput::make('iban')
    ->label('German Bank Account')
    ->countries(['DE'])
    ->rule(function () {
        return function (string $attribute, $value, $fail) {
            if (!str_starts_with($value, 'DE')) {
                $fail('The IBAN must be a German bank account.');
            }
        };
    })
    ->required()
```

### Validate Against Blacklist

```php
IbanInput::make('iban')
    ->label('Bank Account')
    ->rule(function () {
        return function (string $attribute, $value, $fail) {
            $blacklistedIbans = ['UA12345678901234567890123456', /* ... */];

            if (in_array($value, $blacklistedIbans)) {
                $fail('This IBAN is not allowed.');
            }
        };
    })
    ->required()
```

## Testing Examples

### Feature Test

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\BankAccount;
use Granite\FilamentIban\Forms\Components\IbanInput;

class IbanValidationTest extends TestCase
{
    public function test_valid_iban_passes_checksum(): void
    {
        $validIban = 'DE89370400440532013000';

        $this->assertTrue(IbanInput::validateIbanChecksum($validIban));
    }

    public function test_invalid_iban_fails_checksum(): void
    {
        $invalidIban = 'DE89370400440532013001'; // Wrong check digits

        $this->assertFalse(IbanInput::validateIbanChecksum($invalidIban));
    }

    public function test_bank_account_creation_with_valid_iban(): void
    {
        $data = [
            'account_holder' => 'John Doe',
            'iban' => 'DE89370400440532013000',
            'swift_code' => 'COBADEFFXXX',
        ];

        $bankAccount = BankAccount::create($data);

        $this->assertDatabaseHas('bank_accounts', [
            'iban' => 'DE89370400440532013000',
        ]);
    }
}
```

## Valid Test IBANs

For testing purposes, here are some valid IBANs:

```php
// Ukraine
'UA380019000500000026318000212'

// Germany
'DE89370400440532013000'

// Great Britain
'GB82WEST12345698765432'

// France
'FR1420041010050500013M02606'

// Netherlands
'NL91ABNA0417164300'

// Egypt
'EG380019000500000026318000212'
```

## Styling Customization

The component automatically inherits Filament's styling. To customize further, you can override the CSS in your theme:

```css
/* In your app's CSS file */
.fi-iban-input-wrapper {
    /* Custom styles here */
}

.fi-iban-input-wrapper select {
    /* Country selector styles */
}

.fi-iban-input-wrapper input[type="text"] {
    /* Input field styles */
}
```

## API Reference

### Methods

| Method | Parameters | Description |
|--------|------------|-------------|
| `countries()` | `array $countries` | Set available country codes |
| `defaultCountry()` | `string $country` | Set the default country code |
| `validateChecksum()` | `bool $validate` | Enable/disable checksum validation |
| `maxDigits()` | `int $max` | Set maximum number of digits |
| `validateIbanChecksum()` | `string $iban` | Static method to validate IBAN checksum |

### Validation

The component automatically validates:
- Country code is in the allowed list
- Exactly 27 digits (or custom maxDigits)
- Modulo 97 checksum (if enabled)

All validation happens both client-side (Alpine.js) and server-side (PHP).
