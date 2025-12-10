@php
    $id = $getId();
    $isDisabled = $isDisabled();
    $statePath = $getStatePath();
    $countries = $getCountries();
    $defaultCountry = $getDefaultCountry();
    $maxDigits = $getMaxDigits();
    $validateChecksum = $shouldValidateChecksum();

    $cssUrl = \Filament\Support\Facades\FilamentAsset::getStyleHref('filament-iban', package: 'granite/filament-iban');
    $compiledCssUrl = \Illuminate\Support\Js::from($cssUrl);
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <x-filament::input.wrapper
        :disabled="$isDisabled"
        :valid="! $errors->has($statePath)"
        x-data="ibanInputComponent({
            state: $wire.$entangle('{{ $statePath }}'),
            countries: {{ json_encode($countries) }},
            defaultCountry: '{{ $defaultCountry }}',
            maxDigits: {{ $maxDigits }},
            validateChecksum: {{ $validateChecksum ? 'true' : 'false' }},
        })"
        x-load-css="[{{ $compiledCssUrl }}]"
        x-bind:class="{
            'fi-iban-input-wrapper': true,
            'is-valid': isValid === true,
            'is-invalid': isValid === false,
        }"
    >
        <div class="flex items-center">
            {{-- Country Code Select --}}
            <select
                x-model="countryCode"
                @change="updateFullIban"
                :disabled="disabled"
                class="fi-iban-country-select border-0 bg-transparent py-1.5 pl-3 pr-7 text-gray-950 dark:text-white text-sm font-medium focus:ring-0 sm:text-sm sm:leading-6"
            >
                @foreach ($countries as $country)
                    <option value="{{ $country }}">{{ $country }}</option>
                @endforeach
            </select>

            {{-- Divider --}}
            <div class="h-5 w-px bg-gray-200 dark:bg-white/10"></div>

            {{-- IBAN Body Input --}}
            <input
                type="text"
                x-model="displayValue"
                @input="handleInput"
                :disabled="disabled"
                placeholder="00 0000 0000 00000000000000000"
                autocomplete="off"
                spellcheck="false"
                class="fi-iban-input min-w-0 flex-1 border-0 bg-transparent py-1.5 px-3 text-gray-950 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-0 sm:text-sm sm:leading-6 font-mono"
            />

            {{-- Validation Icon --}}
            <div class="flex items-center px-3">
                {{-- Valid --}}
                <template x-if="isValid === true">
                    <div class="flex items-center justify-center w-6 h-6 rounded-full bg-green-100 dark:bg-green-500/20">
                        <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </template>
                {{-- Invalid --}}
                <template x-if="isValid === false">
                    <div class="flex items-center justify-center w-6 h-6 rounded-full bg-red-100 dark:bg-red-500/20">
                        <svg class="h-4 w-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </template>
            </div>
        </div>
    </x-filament::input.wrapper>
</x-dynamic-component>

@script
<script>
    Alpine.data('ibanInputComponent', (config) => ({
        state: config.state,
        countryCode: config.defaultCountry,
        displayValue: '',
        maxDigits: config.maxDigits,
        validateChecksum: config.validateChecksum,
        countries: config.countries,
        fullIban: config.defaultCountry,
        disabled: false,
        isValid: null, // null = no validation yet, true = valid, false = invalid

        init() {
            this.disabled = this.$root.hasAttribute('disabled');

            // Initialize from existing value
            if (this.state) {
                const countryCode = this.state.substring(0, 2);
                const body = this.state.substring(2);

                if (this.countries.includes(countryCode)) {
                    this.countryCode = countryCode;
                    this.displayValue = this.formatIban(body).formatted;
                }
            }

            this.updateValidation();

            // Watch for state changes from outside
            this.$watch('state', (value) => {
                if (value && value !== this.fullIban) {
                    const countryCode = value.substring(0, 2);
                    const body = value.substring(2);

                    if (this.countries.includes(countryCode)) {
                        this.countryCode = countryCode;
                        this.displayValue = this.formatIban(body).formatted;
                        this.updateValidation();
                    }
                }
            });
        },

        formatIban(value) {
            // Strip all non-numeric characters
            const digits = value.replace(/\D/g, '').substring(0, this.maxDigits);

            let formatted = '';

            // Format: XX YYYY ZZZZ AAAAAAAAAAAAAAAAA (2-4-4-17)
            if (digits.length > 0) {
                formatted += digits.substring(0, 2);
            }
            if (digits.length > 2) {
                formatted += ' ' + digits.substring(2, 6);
            }
            if (digits.length > 6) {
                formatted += ' ' + digits.substring(6, 10);
            }
            if (digits.length > 10) {
                formatted += ' ' + digits.substring(10, this.maxDigits);
            }

            return { formatted, digits };
        },

        handleInput() {
            const { formatted, digits } = this.formatIban(this.displayValue);
            this.displayValue = formatted;
            this.updateFullIban();
        },

        updateFullIban() {
            const digits = this.displayValue.replace(/\D/g, '');
            this.fullIban = this.countryCode + digits;
            this.state = this.fullIban;
            this.updateValidation();
        },

        updateValidation() {
            const digits = this.displayValue.replace(/\D/g, '');

            if (digits.length === 0) {
                this.isValid = null;
            } else if (digits.length === this.maxDigits) {
                this.isValid = this.validateChecksum ? this.validateIbanChecksum(this.fullIban) : true;
            } else {
                this.isValid = null;
            }
        },

        validateIbanChecksum(iban) {
            const stripped = iban.replace(/\s+/g, '').toUpperCase();

            if (stripped.length < 4) {
                return false;
            }

            const rearranged = stripped.slice(4) + stripped.slice(0, 4);
            const numeric = rearranged.replace(/[A-Z]/g, char => char.charCodeAt(0) - 55);

            return this.mod97(numeric) === 1;
        },

        mod97(string) {
            let checksum = string.slice(0, 2);

            for (let offset = 2; offset < string.length; offset += 7) {
                const fragment = checksum + string.substring(offset, offset + 7);
                checksum = (parseInt(fragment, 10) % 97).toString();
            }

            return parseInt(checksum, 10);
        },
    }));
</script>
@endscript
