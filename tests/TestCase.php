<?php

declare(strict_types=1);

namespace Granite\FilamentIban\Tests;

use Granite\FilamentIban\FilamentIbanServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            FilamentIbanServiceProvider::class,
        ];
    }
}
