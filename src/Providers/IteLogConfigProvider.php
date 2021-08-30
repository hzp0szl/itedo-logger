<?php

namespace IteLog\Providers;

use Illuminate\Support\ServiceProvider;
use function Ehuidiy\Providers\config_path;

/**
 * 生成日志配置
 *
 * Class IteLogConfigProvider
 * @package IteLog\Providers
 */
class IteLogConfigProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!function_exists('config_path')) {
            function config_path()
            {
                return app()->basePath('config');
            }
        }
        $this->publishes([__DIR__ . '/../config' => config_path()], 'itelog');
    }
}
