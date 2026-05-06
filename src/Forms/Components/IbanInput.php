<?php

declare(strict_types=1);

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

    /**
     * ISO 13616 IBAN body lengths (total IBAN length minus the 2-letter country code).
     * Values represent the number of alphanumeric characters after the country code.
     *
     * @var array<string, int>|Closure
     */
    protected array $countryLengths = [
        'AD' => 22, 'AE' => 21, 'AL' => 26, 'AO' => 23, 'AT' => 18,
        'AZ' => 26, 'BA' => 18, 'BE' => 14, 'BF' => 26, 'BG' => 20,
        'BH' => 20, 'BI' => 25, 'BJ' => 26, 'BR' => 27, 'BY' => 26,
        'CF' => 25, 'CG' => 25, 'CH' => 19, 'CI' => 26, 'CM' => 25,
        'CR' => 20, 'CV' => 23, 'CY' => 26, 'CZ' => 22, 'DE' => 20,
        'DJ' => 25, 'DK' => 16, 'DO' => 26, 'DZ' => 24, 'EE' => 18,
        'EG' => 27, 'ES' => 22, 'FI' => 16, 'FK' => 16, 'FO' => 16,
        'FR' => 25, 'GA' => 25, 'GB' => 20, 'GE' => 20, 'GI' => 21,
        'GL' => 16, 'GQ' => 25, 'GR' => 25, 'GT' => 26, 'GW' => 23,
        'HN' => 26, 'HR' => 19, 'HU' => 26, 'IE' => 20, 'IL' => 21,
        'IQ' => 21, 'IR' => 24, 'IS' => 24, 'IT' => 25, 'JO' => 28,
        'KM' => 25, 'KW' => 28, 'KZ' => 18, 'LB' => 26, 'LC' => 30,
        'LI' => 19, 'LT' => 18, 'LU' => 18, 'LV' => 19, 'LY' => 23,
        'MA' => 26, 'MC' => 25, 'MD' => 22, 'ME' => 20, 'MG' => 25,
        'MK' => 17, 'ML' => 26, 'MN' => 18, 'MR' => 25, 'MT' => 29,
        'MU' => 28, 'MZ' => 23, 'NE' => 26, 'NI' => 26, 'NL' => 16,
        'NO' => 13, 'OM' => 21, 'PK' => 22, 'PL' => 26, 'PS' => 27,
        'PT' => 23, 'QA' => 27, 'RO' => 22, 'RS' => 20, 'RU' => 31,
        'SA' => 22, 'SC' => 29, 'SD' => 16, 'SE' => 22, 'SI' => 17,
        'SK' => 22, 'SM' => 25, 'SN' => 26, 'SO' => 21, 'ST' => 23,
        'SV' => 26, 'TD' => 25, 'TG' => 26, 'TL' => 21, 'TN' => 22,
        'TR' => 24, 'UA' => 27, 'VA' => 20, 'VG' => 22, 'XK' => 18,
        'YE' => 28,
    ];

    protected null|string|Closure $defaultCountry = 'UA';

    protected bool $validateChecksum = true;

    protected int $maxDigits = 27;

    protected bool|Closure $acceptAllCountries = false;

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
        return $this->normalizeCountries($this->evaluate($this->defaultCountries));
    }

    /**
     * Set the per-country IBAN body lengths.
     *
     * @param  array<string, int>|Closure  $countryLengths
     */
    public function countryLengths(array|Closure $countryLengths): static
    {
        $this->countryLengths = $countryLengths;

        return $this;
    }

    /**
     * Get the ISO 13616 per-country IBAN body lengths for frontend validation.
     */
    public function getCountryLengths(): array
    {
        return $this->normalizeCountryLengths($this->evaluate($this->countryLengths));
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
        $country = $this->evaluate($this->defaultCountry);

        return $country !== null ? strtoupper($country) : null;
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

    public function getMaxDigitsForCountry(?string $countryCode = null): int
    {
        if ($countryCode === null) {
            return $this->getMaxDigits();
        }

        return $this->getCountryLengths()[strtoupper($countryCode)] ?? $this->getMaxDigits();
    }

    public static function normalize(string $iban): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper($iban)) ?? '';
    }

    /**
     * Validate IBAN checksum using modulo 97.
     */
    public static function validateIbanChecksum(string $iban): bool
    {
        $stripped = static::normalize($iban);

        if (strlen($stripped) < 4) {
            return false;
        }

        if (! preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]+$/', $stripped)) {
            return false;
        }

        $rearranged = substr($stripped, 4) . substr($stripped, 0, 4);
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

                $normalized = static::normalize((string) $value);
                $countryCode = substr($normalized, 0, 2);

                if (strlen($countryCode) !== 2 || ! ctype_alpha($countryCode)) {
                    $fail("The {$attribute} has an invalid country code.");

                    return;
                }

                if (! $this->acceptsAllCountries() && ! in_array($countryCode, $this->getCountries(), true)) {
                    $fail("The {$attribute} has an invalid country code.");

                    return;
                }

                $countryLengths = $this->getCountryLengths();

                if (! array_key_exists($countryCode, $countryLengths) && $this->acceptsAllCountries()) {
                    $fail("The {$attribute} country code does not support IBAN.");

                    return;
                }

                if (! ctype_digit(substr($normalized, 2, 2))) {
                    $fail("The {$attribute} must contain two numeric check digits after the country code.");

                    return;
                }

                $body = substr($normalized, 2);
                $expectedLength = $countryLengths[$countryCode] ?? $this->getMaxDigits();

                if (strlen($body) !== $expectedLength) {
                    $fail("The {$attribute} must contain exactly {$expectedLength} characters after the country code.");

                    return;
                }

                if ($this->shouldValidateChecksum() && ! static::validateIbanChecksum($normalized)) {
                    $fail("The {$attribute} has an invalid checksum.");
                }
            };
        });
    }

    /**
     * @param  array<int, string>  $countries
     * @return array<int, string>
     */
    private function normalizeCountries(array $countries): array
    {
        return array_values(array_map(
            static fn (string $country): string => strtoupper($country),
            $countries,
        ));
    }

    /**
     * @param  array<string, int>  $countryLengths
     * @return array<string, int>
     */
    private function normalizeCountryLengths(array $countryLengths): array
    {
        $normalized = [];

        foreach ($countryLengths as $country => $length) {
            $normalized[strtoupper((string) $country)] = (int) $length;
        }

        return $normalized;
    }
}
