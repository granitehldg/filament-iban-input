<?php

declare(strict_types=1);

namespace Granite\FilamentIban\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;

class IbanInput extends Field
{
    use HasExtraAlpineAttributes;

    protected string $view = 'filament-iban::forms.components.iban-input';

    protected array $defaultCountries = ['UA', 'EG', 'DE', 'GB', 'FR', 'NL'];

    protected array $countryLengths = [
        'UA' => 27,
        'EG' => 27,
        'DE' => 20,
        'GB' => 20,
        'FR' => 25,
        'NL' => 16,
    ];

    protected ?string $defaultCountry = 'UA';

    protected bool $validateChecksum = true;

    protected int $maxDigits = 27;

    public function countries(array $countries): static
    {
        $this->defaultCountries = array_map(
            static fn (string $country): string => strtoupper($country),
            $countries,
        );

        return $this;
    }

    public function getCountries(): array
    {
        return $this->evaluate($this->defaultCountries);
    }

    public function countryLengths(array $countryLengths): static
    {
        $this->countryLengths = array_change_key_case($countryLengths, CASE_UPPER);

        return $this;
    }

    public function getCountryLengths(): array
    {
        return $this->evaluate($this->countryLengths);
    }

    public function defaultCountry(?string $country): static
    {
        $this->defaultCountry = $country !== null ? strtoupper($country) : null;

        return $this;
    }

    public function getDefaultCountry(): ?string
    {
        return $this->evaluate($this->defaultCountry);
    }

    public function validateChecksum(bool $validate = true): static
    {
        $this->validateChecksum = $validate;

        return $this;
    }

    public function shouldValidateChecksum(): bool
    {
        return $this->evaluate($this->validateChecksum);
    }

    public function maxDigits(int $max): static
    {
        $this->maxDigits = $max;

        return $this;
    }

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

                if (! in_array($countryCode, $this->getCountries(), true)) {
                    $fail("The {$attribute} has an invalid country code.");

                    return;
                }

                if (! ctype_digit(substr($normalized, 2, 2))) {
                    $fail("The {$attribute} must contain two numeric check digits after the country code.");

                    return;
                }

                $body = substr($normalized, 2);
                $expectedLength = $this->getMaxDigitsForCountry($countryCode);

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
}
