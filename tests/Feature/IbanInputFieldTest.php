<?php

declare(strict_types=1);

namespace Granite\FilamentIban\Tests\Feature;

use Filament\Support\Facades\FilamentAsset;
use Granite\FilamentIban\Forms\Components\IbanInput;
use Granite\FilamentIban\Tests\TestCase;

final class IbanInputFieldTest extends TestCase
{
    public static function validIbanProvider(): array
    {
        return [
            'egypt' => ['EG380019000500000000263180002'],
            'germany' => ['DE89370400440532013000'],
            'united kingdom' => ['GB29NWBK60161331926819'],
            'france' => ['FR1420041010050500013M02606'],
            'netherlands' => ['NL91ABNA0417164300'],
            'ukraine' => ['UA213223130000026007233566001'],
        ];
    }

    public function test_it_validates_known_valid_ibans(): void
    {
        foreach (self::validIbanProvider() as [$iban]) {
            self::assertTrue(IbanInput::validateIbanChecksum($iban), $iban);
        }
    }

    public function test_it_rejects_known_invalid_ibans(): void
    {
        self::assertFalse(IbanInput::validateIbanChecksum('EG00019000500000000263180002'));
        self::assertFalse(IbanInput::validateIbanChecksum('GB29NWBK60161331926818'));
        self::assertFalse(IbanInput::validateIbanChecksum('GBHYNWBK60161331926819'));
    }

    public function test_it_accepts_supported_country_lengths_and_alphanumeric_bodies(): void
    {
        $field = IbanInput::make('iban');

        foreach (self::validIbanProvider() as [$iban]) {
            self::assertSame([], $this->validateField($field, $iban), $iban);
        }
    }

    public function test_it_accepts_any_known_iban_country_when_all_countries_is_enabled(): void
    {
        $field = IbanInput::make('iban')->allCountries();

        self::assertSame([], $this->validateField($field, 'SA0380000000608010167519'));
    }

    public function test_it_preserves_country_configuration_normalization(): void
    {
        $field = IbanInput::make('iban')
            ->countries(['eg', 'gb'])
            ->defaultCountry('eg');

        self::assertSame(['EG', 'GB'], $field->getCountries());
        self::assertSame('EG', $field->getDefaultCountry());
    }

    public function test_it_preserves_custom_country_lengths_configuration(): void
    {
        $field = IbanInput::make('iban')
            ->countries(['ZZ'])
            ->countryLengths(['zz' => 6])
            ->validateChecksum(false);

        self::assertSame([], $this->validateField($field, 'ZZ123456'));
    }

    public function test_it_rejects_values_with_the_wrong_country_specific_length(): void
    {
        $field = IbanInput::make('iban');
        $errors = $this->validateField($field, 'GB29NWBK6016133192681');

        self::assertSame([
            'The iban must contain exactly 20 characters after the country code.',
        ], $errors);
    }

    public function test_it_rejects_non_numeric_check_digits(): void
    {
        $field = IbanInput::make('iban');
        $errors = $this->validateField($field, 'GBHYNWBK60161331926819');

        self::assertSame([
            'The iban must contain two numeric check digits after the country code.',
        ], $errors);
    }

    public function test_it_registers_the_stylesheet_asset(): void
    {
        $href = FilamentAsset::getStyleHref('filament-iban', package: 'granite/filament-iban');

        self::assertIsString($href);
        self::assertNotSame('', $href);
    }

    public function test_the_view_preserves_ltr_direction_after_merging_main(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/forms/components/iban-input.blade.php');

        self::assertStringNotContainsString('<<<<<<<', $view);
        self::assertStringNotContainsString('=======', $view);
        self::assertStringNotContainsString('>>>>>>>', $view);
        self::assertStringContainsString('<div class="flex items-center" dir="ltr">', $view);
        self::assertGreaterThanOrEqual(2, substr_count($view, 'dir="ltr"'));
    }

    private function validateField(IbanInput $field, string $value): array
    {
        $errors = [];
        $rules = $field->getValidationRules();
        $rule = $rules[1];

        $rule('iban', $value, function (string $message) use (&$errors): void {
            $errors[] = $message;
        });

        return $errors;
    }
}
