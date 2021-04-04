<?php

namespace Nerio\LaravelStub;

use Illuminate\Support\ServiceProvider;
use Nerio\LaravelStub\Commands\MakeCommand;
use function CaoJiayuan\Utility\file_map as file_mapAlias;

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

    public function boot()
    {
        $this->autoloadApps();
    }

    protected function autoloadApps()
    {
        $config = $this->app['config'];

        $root = $config->get('stub.app_path');
        file_mapAlias(base_path($root), function ($dir, \SplFileInfo $info, $isdir) use ($root) {

            if ($isdir) {
                $appName = $info->getBasename();
                $serviceProvider = ucfirst($root) . "\\{$appName}\\{$appName}ServiceProvider";

                if (class_exists($serviceProvider)) {
                    $this->app->register($serviceProvider);
                }
            }
        }, false);
    }
}
