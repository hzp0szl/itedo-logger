<?php

namespace IteLog\Providers;

use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use IteLog\Facades\IteLogFacades;
use IteLog\Service\IteLogService;

/**
 * Class ReqResLoggerProvider
 * @package IteLog\Providers
 */
class IteLoggerProvider extends ServiceProvider
{
    /**
     * @var array
     */
    private $result = [];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('iteLog', function () {
            return new IteLogService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mapPublish();
        $this->setSql();
    }

    /**
     * 生成配置
     */
    public function mapPublish()
    {
        $this->publishes([__DIR__ . '/../config' => app()->basePath('config')], 'itelog');
    }

    /**
     * sql语句调试
     */
    public function setSql()
    {
        $logger = config('itelog.logger');
        if ($logger) {
            DB::listen(function ($query) {
                $sql = str_replace('?', '"' . '%s' . '"', $query->sql);
                $qBindings = [];
                foreach ($query->bindings as $key => $value) {
                    if (is_numeric($key)) {
                        $qBindings[] = $value;
                    } else {
                        $sql = str_replace(':' . $key, '"' . $value . '"', $sql);
                    }
                }
                if (!empty($qBindings)) {
                    $sql = vsprintf($sql, $qBindings);
                }
                $sql = str_replace("\\", "", $sql);
                //
//                $this->result[] = [
//                    'startTime' => Carbon::now()->toDate()->format('Y-m-d H:i:s.u'),
//                    'executionTime' => $query->time . 'ms;',
//                    'sql' => $sql,
//                ];
                // 匹配thinkphp格式
                $date = Carbon::now()->toDate()->format('Y-m-d H:i:s.u');
                $this->result[] = '[ SQL ] ' . $sql . ' [ ' . $date . ' RunTime:' . $query->time . 'ms ]';
                //设置数据
                IteLogFacades::setSqlList($this->result);
            });
        }
    }
}
