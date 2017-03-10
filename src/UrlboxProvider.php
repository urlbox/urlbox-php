<?php

namespace Urlbox\Screenshots;

use Illuminate\Support\ServiceProvider;

class UrlboxProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('urlbox',function() {
            $config = config('services.urlbox');
            return new Urlbox($config['key'], $config['secret']);
        });
    }
}