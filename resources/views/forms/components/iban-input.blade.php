@php
    $isDisabled = $isDisabled();
    $statePath = $getStatePath();
    $countries = $getCountries();
    $countryLengths = $getCountryLengths();
    $defaultCountry = $getDefaultCountry();
    $fallbackMaxDigits = $getMaxDigits();
    $validateChecksum = $shouldValidateChecksum();
    $acceptAllCountries = $acceptsAllCountries();

    $cssUrl = \Filament\Support\Facades\FilamentAsset::getStyleHref('filament-iban-input', package: 'granitehldg/filament-iban-input');
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
            acceptAllCountries: {{ $acceptAllCountries ? 'true' : 'false' }},
        })"
        x-load-css="[{{ $compiledCssUrl }}]"
        x-bind:class="{
            'fi-iban-input-wrapper': true,
            'is-valid': isValid === true,
            'is-invalid': isValid === false,
        }"
    >
        <div class="fi-iban-row" dir="ltr">
            @if (! $acceptAllCountries)
                @if (count($countries) === 1)
                    <span class="fi-iban-country-label">{{ $countries[0] }}</span>
                @else
                    <select
                        x-model="countryCode"
                        @change="updateFullIban"
                        :disabled="disabled"
                        class="fi-iban-country-select"
                    >
                        @foreach ($countries as $country)
                            <option value="{{ $country }}">{{ $country }}</option>
                        @endforeach
                    </select>
                @endif

                <div class="fi-iban-divider"></div>

                <input
                    type="text"
                    x-model="displayValue"
                    @input="handleInput"
                    :disabled="disabled"
                    placeholder="00 0000 0000 0000 0000 0000 000"
                    autocomplete="off"
                    spellcheck="false"
                    dir="ltr"
                    id="{{ $getId() }}"
                    class="fi-iban-input"
                />
            @else
                <input
                    type="text"
                    x-model="freeFormValue"
                    @input="handleFreeFormInput"
                    :disabled="disabled"
                    placeholder="{{ $getPlaceholder() ?? 'XX00 0000 0000 0000 0000 0000 0000 00' }}"
                    autocomplete="off"
                    spellcheck="false"
                    dir="ltr"
                    id="{{ $getId() }}"
                    class="fi-iban-input"
                />
            @endif

            <div class="fi-iban-status">
                <template x-if="isValid === true">
                    <div class="fi-iban-status-badge fi-iban-status-valid">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </template>
                <template x-if="isValid === false">
                    <div class="fi-iban-status-badge fi-iban-status-invalid">
                        <svg fill="currentColor" viewBox="0 0 20 20">
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
        freeFormValue: '',
        fallbackMaxDigits: config.fallbackMaxDigits,
        validateChecksum: config.validateChecksum,
        countries: config.countries,
        countryLengths: config.countryLengths,
        acceptAllCountries: config.acceptAllCountries,
        fullIban: config.defaultCountry,
        disabled: false,
        isValid: null,

        init() {
            this.disabled = this.$root.hasAttribute('disabled');

            if (this.acceptAllCountries) {
                if (this.state) {
                    this.freeFormValue = this.formatFreeForm(this.state);
                    this.updateValidation();
                }

                this.$watch('state', (value) => {
                    if (! value) {
                        this.freeFormValue = '';
                        this.isValid = null;

                        return;
                    }

                    const normalized = this.normalize(value);

                    if (normalized !== this.normalize(this.freeFormValue)) {
                        this.freeFormValue = this.formatFreeForm(normalized);
                    }

                    this.updateValidation();
                });
            } else {
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
            }
        },

        normalize(value) {
            return value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        },

        formatFreeForm(value) {
            const normalized = this.normalize(value);
            const country = normalized.substring(0, 2);
            const expectedBodyLen = this.countryLengths[country] ?? null;
            const maxLen = expectedBodyLen ? 2 + expectedBodyLen : null;
            const capped = maxLen ? normalized.substring(0, maxLen) : normalized;
            const parts = capped.match(/.{1,4}/g) ?? [];

            return parts.join(' ');
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
            // A body starts with check digits, so leading letters are a pasted country code.
            const pastedCountry = this.normalize(this.displayValue).substring(0, 2);

            if (/^[A-Z]{2}$/.test(pastedCountry) && this.countries.includes(pastedCountry)) {
                this.countryCode = pastedCountry;
                this.displayValue = this.normalize(this.displayValue).substring(2);
            }

            this.updateFullIban();
        },

        updateFullIban() {
            const { formatted, normalized } = this.formatBody(this.displayValue);
            this.displayValue = formatted;
            this.fullIban = this.countryCode + normalized;
            this.state = normalized === '' ? null : this.fullIban;
            this.updateValidation();
        },

        handleFreeFormInput() {
            const formatted = this.formatFreeForm(this.freeFormValue);
            this.freeFormValue = formatted;
            this.state = this.normalize(formatted);
            this.updateValidation();
        },

        updateValidation() {
            if (this.acceptAllCountries) {
                const normalized = this.normalize(this.freeFormValue);

                if (normalized.length < 2) {
                    this.isValid = null;

                    return;
                }

                const country = normalized.substring(0, 2);

                // Reject non-IBAN countries immediately after 2 chars are typed
                if (! (country in this.countryLengths)) {
                    this.isValid = false;

                    return;
                }

                const expectedBodyLen = this.countryLengths[country];

                if (normalized.length < 2 + expectedBodyLen) {
                    this.isValid = null;

                    return;
                }

                if (normalized.length === 2 + expectedBodyLen) {
                    this.isValid = this.validateChecksum ? this.validateIbanChecksum(normalized) : true;
                } else {
                    this.isValid = null;
                }
            } else {
                const body = this.normalize(this.displayValue);

                if (body.length === 0) {
                    this.isValid = null;
                } else if (body.length === this.maxLengthForCountry()) {
                    this.isValid = this.validateChecksum ? this.validateIbanChecksum(this.fullIban) : true;
                } else {
                    this.isValid = null;
                }
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
