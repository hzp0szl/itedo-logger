<?php

namespace IteLog\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use IteLog\Facades\IteLogFacades;

/**
 * 请求及相应日志中间件
 *
 * Class ReqResLogger
 * @package IteLog\Http\Middleware
 */
class ReqResLogger
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return mixed|void
     */
    public function handle($request, Closure $next)
    {
        //响应数据
        $response = $next($request);
        $inData = [
            'request' => [
                'secure' => $request->getScheme(),
                'ipPort' => $request->getClientIp().':'.$request->getPort(),
                'methodUri' => $request->getMethod() . ':' . $request->getRequestUri(),
                'url' => $request->getUri(),
            ],
            'authorization' => $request->server('HTTP_AUTHORIZATION'),
            'resource' => [
                'sqlArr' => IteLogFacades::getSqlList(),
                'response' => $response->getContent() ?: '',
                'status' => $response->getStatusCode()
            ]
        ];
        dump($inData);
        return $response;
    }

    /**
     * sql语句调试
     */
    public function sqlDebug(): array
    {
        $logger = config('itelog.logger');
        $result = [];
        if ($logger) {
            DB::listen(function ($query) use (&$result) {
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
                $result[] = [
                    'executionTime' => $query->time . 'ms;',
                    'sql' => $sql,
                ];
            });
        }
        return $result;
    }
}
