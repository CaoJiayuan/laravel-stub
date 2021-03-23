<?php

namespace Nerio\LaravelStub;

use Illuminate\Support\ServiceProvider;
use Nerio\LaravelStub\Commands\MakeCommand;

/**
 * @author caojiayuan
 */
class StubServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/stub.php', 'stub');
        $this->registerCommands();
    }

    protected function registerCommands()
    {
        $this->app->singleton('command.stub.make', function ($app) {
            return new MakeCommand($app['config']->get('stub'));
        });

        $this->commands(['command.stub.make']);
    }
}
