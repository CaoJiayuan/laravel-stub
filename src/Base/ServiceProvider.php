<?php namespace Nerio\LaravelStub\Base;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * @author caojiayuan
 */
class ServiceProvider extends BaseServiceProvider
{
  protected $prefix = '';

  protected function getRoute()
  {
    return Route::prefix($this->prefix);
  }

  protected function subModule($appRoute, $middleware = [], $prefix = '')
  {
    $parts = explode('.', $appRoute);
    $route = 'routes';
    $app = $parts[0];
    if (count($parts) > 1) {
      $route = $parts[1];
    }

    $appPath = application_path($app);

    $routePath = $appPath . DIRECTORY_SEPARATOR . $route . '.php';
    $config = $this->app['config'];

    $root = $config->get('stub.app_path');
    $namespace = ucfirst($root) . "\\" . ucfirst($app) . "\\Controllers";
    return $this->getRoute()
      ->middleware($middleware)
      ->namespace($namespace)
      ->group(function ($router) use ($prefix, $routePath) {
        $router->prefix($prefix)->group($routePath);
      });
  }
}
