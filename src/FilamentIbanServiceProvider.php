<?php

declare(strict_types=1);

namespace Granite\FilamentIban;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentIbanServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-iban')
            ->hasViews();
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('filament-iban', __DIR__ . '/../resources/dist/filament-iban.css')
                ->loadedOnRequest(),
        ], 'granite/filament-iban');
    }
}
