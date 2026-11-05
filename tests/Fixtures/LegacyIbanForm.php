<?php

declare(strict_types=1);

namespace Granite\FilamentIban\Tests\Fixtures;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Granite\FilamentIban\Forms\Components\IbanInput;
use Livewire\Component;

// Filament 3 form host.
class LegacyIbanForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema([IbanInput::make('iban')])->statePath('data');
    }

    public function render(): string
    {
        return '<div>{{ $this->form }}</div>';
    }
}
