<?php

namespace Dms\Web\Laravel\Language;

use Dms\Core\Language\ILanguageProvider;
use Illuminate\Support\ServiceProvider;

/**
 * The language service provider
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LanguageServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ILanguageProvider::class, LaravelLanguageProvider::class);
    }
}