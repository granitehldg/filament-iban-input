<?php

declare(strict_types=1);

namespace Granite\FilamentIban\Tests\Fixtures;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Granite\FilamentIban\Forms\Components\IbanInput;
use Livewire\Component;

// Filament 4/5 form host. Filament 3 types form() differently, see LegacyIbanForm.
class IbanForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([IbanInput::make('iban')])->statePath('data');
    }

    public function render(): string
    {
        return '<div>{{ $this->form }}</div>';
    }
}
