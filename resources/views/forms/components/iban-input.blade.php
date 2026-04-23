@php
    $id = $getId();
    $isDisabled = $isDisabled();
    $statePath = $getStatePath();
    $countries = $getCountries();
    $countryLengths = $getCountryLengths();
    $defaultCountry = $getDefaultCountry();
    $fallbackMaxDigits = $getMaxDigits();
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
            countryLengths: {{ json_encode($countryLengths) }},
            defaultCountry: '{{ $defaultCountry }}',
            fallbackMaxDigits: {{ $fallbackMaxDigits }},
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

            <div class="h-5 w-px bg-gray-200 dark:bg-white/10"></div>

            <input
                type="text"
                x-model="displayValue"
                @input="handleInput"
                :disabled="disabled"
                placeholder="00 0000 0000 0000 0000 0000 000"
                autocomplete="off"
                spellcheck="false"
                class="fi-iban-input min-w-0 flex-1 border-0 bg-transparent py-1.5 px-3 text-gray-950 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-0 sm:text-sm sm:leading-6 font-mono uppercase"
            />

            <div class="flex items-center px-3">
                <template x-if="isValid === true">
                    <div class="flex items-center justify-center w-6 h-6 rounded-full bg-green-100 dark:bg-green-500/20">
                        <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </template>
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
        fallbackMaxDigits: config.fallbackMaxDigits,
        validateChecksum: config.validateChecksum,
        countries: config.countries,
        countryLengths: config.countryLengths,
        fullIban: config.defaultCountry,
        disabled: false,
        isValid: null,

        init() {
            this.disabled = this.$root.hasAttribute('disabled');

            if (this.state) {
                this.syncFromState(this.state);
            } else {
                this.resetState();
            }

            this.$watch('state', (value) => {
                if (! value) {
                    this.resetState();

                    return;
                }

                const normalized = this.normalize(value);

                if (normalized === this.fullIban) {
                    return;
                }

                this.syncFromState(normalized);
            });
        },

        normalize(value) {
            return value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        },

        maxLengthForCountry() {
            return this.countryLengths[this.countryCode] ?? this.fallbackMaxDigits;
        },

        formatBody(value) {
            const normalized = this.normalize(value).substring(0, this.maxLengthForCountry());
            const parts = normalized.match(/.{1,4}/g) ?? [];

            return {
                formatted: parts.join(' '),
                normalized,
            };
        },

        resetState() {
            this.displayValue = '';
            this.fullIban = this.countryCode;
            this.isValid = null;
        },

        syncFromState(value) {
            const normalized = this.normalize(value);
            const countryCode = normalized.substring(0, 2);
            const body = normalized.substring(2);

            if (this.countries.includes(countryCode)) {
                this.countryCode = countryCode;
                const formattedBody = this.formatBody(body);
                this.displayValue = formattedBody.formatted;
                this.fullIban = this.countryCode + formattedBody.normalized;
            } else {
                this.resetState();
            }

            this.updateValidation();
        },

        handleInput() {
            const { formatted } = this.formatBody(this.displayValue);
            this.displayValue = formatted;
            this.updateFullIban();
        },

        updateFullIban() {
            const { formatted, normalized } = this.formatBody(this.displayValue);
            this.displayValue = formatted;
            this.fullIban = this.countryCode + normalized;
            this.state = this.fullIban;
            this.updateValidation();
        },

        updateValidation() {
            const body = this.normalize(this.displayValue);

            if (body.length === 0) {
                this.isValid = null;
            } else if (body.length === this.maxLengthForCountry()) {
                this.isValid = this.validateChecksum ? this.validateIbanChecksum(this.fullIban) : true;
            } else {
                this.isValid = null;
            }
        },

        validateIbanChecksum(iban) {
            const stripped = this.normalize(iban);

            if (stripped.length < 4) {
                return false;
            }

            if (! /^[A-Z]{2}\d{2}[A-Z0-9]+$/.test(stripped)) {
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
