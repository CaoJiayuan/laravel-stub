<?php

namespace Nerio\LaravelStub\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use function CaoJiayuan\Utility\file_map;

/**
 * @author caojiayuan
 */
class MakeCommand extends Command
{
    protected $signature = 'make:stub {app : app name}';

    protected $description = '新建模块应用';
    private $config;

    public function __construct($config)
    {
        parent::__construct();
        $this->config = $config;
    }

    public function handle()
    {

        $stdDir = __DIR__ . '/../../stub';
        $appPath = $this->appPath();
        if (file_exists(base_path($appPath))) {
            $this->warn("app exists!");
            return 1;
        }
        $this->mkdir();

        $replaces = $this->getReplaces();

        file_map($stdDir, function ($path, \SplFileInfo $info, $isDir) use ($appPath, $stdDir, $replaces) {
            $p = str_replace(realpath($stdDir), '', realpath($path));

            $filepath = base_path($appPath . $p);
            if ($isDir) {
                @mkdir($filepath, 0755, true);
                $this->info(sprintf("make application dir %s", $filepath));
            } else {
                $content = file_get_contents($path);
                $replacer = function ($content) use ($replaces) {
                    return preg_replace_callback('/\${(.*?)}/', function ($match) use ($replaces) {
                        return Arr::get($replaces, $match[1]);
                    }, $content);
                };
                if (Str::contains($filepath, '.stub')) {
                    $filepath = str_replace('.stub', '', $filepath);
                    $content = $replacer($content);
                }

                $filepath = $replacer($filepath);

                file_put_contents($filepath, $content);
            }
        });

        return 0;
    }

    protected function mkdir()
    {
        $dir = $this->appPath();

        if (!file_exists($dir)) {
            @mkdir(base_path($dir), 0775, true);

            $this->info(sprintf('make application dir %s', $dir));
        }
    }

    protected function appPath()
    {
        $name = $this->appName();

        return  $this->config['app_path'] . '/' . $name;
    }

    protected function appName()
    {
        $name = $this->argument('app');

        return ucfirst(Str::camel($name));
    }

    /**
     * @return array
     */
    protected function getReplaces()
    {
        return [
            'AppPath' => ucfirst($this->config['app_path']),
            'AppName' => $this->appName(),
            'appName' => lcfirst($this->appName()),
        ];
    }
}
