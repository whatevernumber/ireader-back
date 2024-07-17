<?php

namespace App\Providers;

use Bitverse\Identicon\Color\Color;
use Bitverse\Identicon\Generator\PixelsGenerator;
use Bitverse\Identicon\Identicon;
use Bitverse\Identicon\Preprocessor\MD5Preprocessor;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AvatarServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Identicon::class, function(Application $app) {
            $pixelGenerator = $app->make(PixelsGenerator::class);
            $pixelGenerator->setBackgroundColor(Color::parseHex('#EEEEEE'));

            return new Identicon($app->make(MD5Preprocessor::class), $pixelGenerator);
        });
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
