<?php
namespace Urlbox\Screenshots;
use Illuminate\Support\ServiceProvider;
class RendererProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'urlbox',
            'Urlbox\Screenshots'
        );
    }
}