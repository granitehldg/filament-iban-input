<?php

namespace Granite\FilamentIban\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;

class IbanInput extends Field
{
    use HasExtraAlpineAttributes;

    protected string $view = 'filament-iban::forms.components.iban-input';

    protected array $defaultCountries = ['UA', 'EG', 'DE', 'GB', 'FR', 'NL'];

    protected ?string $defaultCountry = 'UA';

    protected bool $validateChecksum = true;

    protected int $maxDigits = 27;

    /**
     * Set the available country codes
     */
    public function countries(array $countries): static
    {
        $this->defaultCountries = $countries;

        return $this;
    }

    /**
     * Get the available country codes
     */
    public function getCountries(): array
    {
        return $this->evaluate($this->defaultCountries);
    }

    /**
     * Set the default country code
     */
    public function defaultCountry(?string $country): static
    {
        $this->defaultCountry = $country;

        return $this;
    }

    /**
     * Get the default country code
     */
    public function getDefaultCountry(): ?string
    {
        return $this->evaluate($this->defaultCountry);
    }

    /**
     * Enable or disable checksum validation
     */
    public function validateChecksum(bool $validate = true): static
    {
        $this->validateChecksum = $validate;

        return $this;
    }

    /**
     * Check if checksum validation is enabled
     */
    public function shouldValidateChecksum(): bool
    {
        return $this->evaluate($this->validateChecksum);
    }

    /**
     * Set the maximum number of digits
     */
    public function maxDigits(int $max): static
    {
        $this->maxDigits = $max;

        return $this;
    }

    /**
     * Get the maximum number of digits
     */
    public function getMaxDigits(): int
    {
        return $this->evaluate($this->maxDigits);
    }

    /**
     * Validate IBAN checksum using modulo 97
     */
    public static function validateIbanChecksum(string $iban): bool
    {
        // Remove spaces and make uppercase
        $stripped = str_replace(' ', '', strtoupper($iban));

        // Must be at least 4 characters (country code + check digits)
        if (strlen($stripped) < 4) {
            return false;
        }

        // Move first 4 characters to the end
        $rearranged = substr($stripped, 4).substr($stripped, 0, 4);

        // Convert letters to numbers (A=10, B=11, ..., Z=35)
        $numeric = '';
        for ($i = 0, $iMax = strlen($rearranged); $i < $iMax; $i++) {
            $char = $rearranged[$i];
            if (ctype_alpha($char)) {
                $numeric .= (string) (ord($char) - 55);
            } else {
                $numeric .= $char;
            }
        }

        // Calculate remainder using bcmod for large numbers
        return bcmod($numeric, '97') === '1';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule(function () {
            return function (string $attribute, $value, $fail) {
                if (empty($value)) {
                    return;
                }

                // Extract country code and body
                $countryCode = substr($value, 0, 2);
                $body = substr($value, 2);

                // Check if country code is valid
                if (! in_array($countryCode, $this->getCountries(), true)) {
                    $fail("The {$attribute} has an invalid country code.");

                    return;
                }

                // Remove spaces and validate length
                $digitsOnly = preg_replace('/\D/', '', $body);

                if (strlen($digitsOnly) !== $this->getMaxDigits()) {
                    $fail("The {$attribute} must contain exactly {$this->getMaxDigits()} digits.");

                    return;
                }

                // Validate checksum if enabled
                if ($this->shouldValidateChecksum() && ! static::validateIbanChecksum($value)) {
                    $fail("The {$attribute} has an invalid checksum.");
                }
            };
        });
    }
}
