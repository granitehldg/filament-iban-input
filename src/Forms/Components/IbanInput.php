<?php

namespace Granite\FilamentIban\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;
use Filament\Support\Concerns\HasPlaceholder;

class IbanInput extends Field
{
    use HasExtraAlpineAttributes;
    use HasPlaceholder;

    protected string $view = 'filament-iban::forms.components.iban-input';

    protected array|Closure $defaultCountries = ['UA', 'EG', 'DE', 'GB', 'FR', 'NL'];

    protected null|string|Closure $defaultCountry = 'UA';

    protected bool $validateChecksum = true;

    protected int $maxDigits = 27;

    protected bool|Closure $acceptAllCountries = false;

    /**
     * ISO 13616 IBAN body lengths (total IBAN length minus the 2-letter country code).
     * Values represent the number of alphanumeric characters after the country code.
     *
     * @var array<string, int>
     */
    protected array $countryLengths = [
        'AD' => 22, 'AE' => 21, 'AL' => 26, 'AT' => 18, 'AZ' => 26,
        'BA' => 18, 'BE' => 14, 'BG' => 20, 'BH' => 20, 'BR' => 27,
        'BY' => 26, 'CH' => 19, 'CR' => 20, 'CY' => 26, 'CZ' => 22,
        'DE' => 20, 'DK' => 16, 'DO' => 26, 'EE' => 18, 'EG' => 27,
        'ES' => 22, 'FI' => 16, 'FO' => 16, 'FR' => 25, 'GB' => 20,
        'GE' => 20, 'GI' => 21, 'GL' => 16, 'GR' => 25, 'GT' => 26,
        'HR' => 19, 'HU' => 26, 'IE' => 20, 'IL' => 21, 'IQ' => 21,
        'IS' => 24, 'IT' => 25, 'JO' => 28, 'KW' => 28, 'KZ' => 18,
        'LB' => 26, 'LC' => 30, 'LI' => 19, 'LT' => 18, 'LU' => 18,
        'LV' => 19, 'MC' => 25, 'MD' => 22, 'ME' => 20, 'MK' => 17,
        'MR' => 25, 'MT' => 29, 'MU' => 28, 'NL' => 16, 'NO' => 13,
        'PK' => 22, 'PL' => 26, 'PS' => 27, 'PT' => 23, 'QA' => 27,
        'RO' => 22, 'RS' => 20, 'SA' => 22, 'SC' => 29, 'SD' => 16,
        'SE' => 22, 'SI' => 17, 'SK' => 22, 'SM' => 25, 'ST' => 23,
        'SV' => 26, 'TL' => 21, 'TN' => 22, 'TR' => 24, 'UA' => 27,
        'VA' => 20, 'VG' => 22, 'XK' => 18, 'YE' => 28,
    ];

    /**
     * Set the available country codes. Accepts a plain array or a Closure returning an array.
     */
    public function countries(array|Closure $countries): static
    {
        $this->defaultCountries = $countries;

        return $this;
    }

    /**
     * Allow any IBAN country code, bypassing the country whitelist.
     * Checksum validation still applies.
     * Accepts a boolean or a Closure returning a boolean.
     */
    public function allCountries(bool|Closure $condition = true): static
    {
        $this->acceptAllCountries = $condition;

        return $this;
    }

    /**
     * Get the available country codes.
     */
    public function getCountries(): array
    {
        return $this->evaluate($this->defaultCountries);
    }

    /**
     * Get the ISO 13616 per-country IBAN body lengths for frontend validation.
     */
    public function getCountryLengths(): array
    {
        return $this->countryLengths;
    }

    /**
     * Whether all country codes are accepted.
     */
    public function acceptsAllCountries(): bool
    {
        return (bool) $this->evaluate($this->acceptAllCountries);
    }

    /**
     * Set the default country code. Accepts a string, null, or a Closure.
     */
    public function defaultCountry(null|string|Closure $country): static
    {
        $this->defaultCountry = $country;

        return $this;
    }

    /**
     * Get the default country code.
     */
    public function getDefaultCountry(): ?string
    {
        return $this->evaluate($this->defaultCountry);
    }

    /**
     * Enable or disable checksum validation.
     */
    public function validateChecksum(bool $validate = true): static
    {
        $this->validateChecksum = $validate;

        return $this;
    }

    /**
     * Check if checksum validation is enabled.
     */
    public function shouldValidateChecksum(): bool
    {
        return $this->evaluate($this->validateChecksum);
    }

    /**
     * Set the maximum number of digits.
     */
    public function maxDigits(int $max): static
    {
        $this->maxDigits = $max;

        return $this;
    }

    /**
     * Get the maximum number of digits.
     */
    public function getMaxDigits(): int
    {
        return $this->evaluate($this->maxDigits);
    }

    /**
     * Validate IBAN checksum using modulo 97.
     */
    public static function validateIbanChecksum(string $iban): bool
    {
        $stripped = str_replace(' ', '', strtoupper($iban));

        if (strlen($stripped) < 4) {
            return false;
        }

        $rearranged = substr($stripped, 4).substr($stripped, 0, 4);
        $numeric = '';

        for ($i = 0, $iMax = strlen($rearranged); $i < $iMax; $i++) {
            $char = $rearranged[$i];
            $numeric .= ctype_alpha($char) ? (string) (ord($char) - 55) : $char;
        }

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

                $countryCode = strtoupper(substr($value, 0, 2));
                $body = substr($value, 2);

                if (strlen($countryCode) !== 2 || ! ctype_alpha($countryCode)) {
                    $fail("The {$attribute} has an invalid country code.");

                    return;
                }

                if (! $this->acceptsAllCountries() && ! in_array($countryCode, $this->getCountries(), true)) {
                    $fail("The {$attribute} has an invalid country code.");

                    return;
                }

                // Even in allCountries() mode, the country code must be a known IBAN-issuing country
                if (! array_key_exists($countryCode, $this->countryLengths)) {
                    $fail("The {$attribute} country code does not support IBAN.");

                    return;
                }

                // Validate body length against known country spec, or maxDigits fallback
                $expectedBodyLength = $this->countryLengths[$countryCode] ?? null;

                if ($expectedBodyLength !== null) {
                    $bodyNormalized = preg_replace('/[^A-Z0-9]/', '', strtoupper($body));

                    if (strlen($bodyNormalized) !== $expectedBodyLength) {
                        $fail("The {$attribute} is not the correct length for a {$countryCode} IBAN.");

                        return;
                    }
                } elseif (! $this->acceptsAllCountries()) {
                    // Unknown country in restricted mode — fall back to maxDigits
                    $bodyNormalized = preg_replace('/[^A-Z0-9]/', '', strtoupper($body));

                    if (strlen($bodyNormalized) !== $this->getMaxDigits()) {
                        $fail("The {$attribute} must contain exactly {$this->getMaxDigits()} characters.");

                        return;
                    }
                }

                if ($this->shouldValidateChecksum() && ! static::validateIbanChecksum($value)) {
                    $fail("The {$attribute} has an invalid checksum.");
                }
            };
        });
    }
}
