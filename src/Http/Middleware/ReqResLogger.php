<?php

namespace IteLog\Http\Middleware;

use Closure;
use Doctrine\DBAL\Driver\PDOConnection;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use IteLog\Facades\IteLogFacades;
use PDO;

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
        $startUsage = memory_get_usage();
        IteLogFacades::setStartTime();
        $start = IteLogFacades::getStartTime();
        $response = $next($request);
        $inData = [
            'source' => 'laravel-itedo-logger',
            'startTime' => $start,
            'request' => $this->request($request),
            'headers' => $this->headers($request)
        ];
        IteLogFacades::setStartTime();
        $stop = IteLogFacades::getStartTime();
        $inData['stopTime'] = $stop;
        $inData['resource'] = $this->resource($response, $start, $stop, $startUsage);
        $inData['diffTime'] = carbon::parse($start)->diffInMilliseconds($stop, false) . ' ms';
        //
        IteLogFacades::into($inData);
        return $response;
    }

    /**
     * @param $request
     * @return array
     */
    private function request($request): array
    {
        return [
            'secure' => $request->getScheme(),
            'ipPort' => $request->getClientIp() . ':' . $request->getPort(),
            'methodUri' => $request->getMethod() . ':' . $request->getRequestUri(),
            'url' => $request->getUri(),
            'param' => $request->all(),
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    private function headers(\Illuminate\Http\Request $request): array
    {
        $collect = collect($request->headers->all())->map(function ($head) {
            return $head[0];
        });
        $collect->put('cookie', $request->cookie() ?? '');
        $collect->put('server_protocol', $request->server('SERVER_PROTOCOL'));
        $collect->put('request_time_float', $request->server('REQUEST_TIME_FLOAT'));
        $collect->put('request_time', date('Y-m-d H:i:s', $request->server('REQUEST_TIME')));
        return $collect->all();
    }

    /**
     * @param $response
     * @param $start
     * @param $stop
     * @param $startUsage
     * @return array
     */
    private function resource($response, $start, $stop, $startUsage): array
    {
        $pdo = DB::getPdo();
        $runtime = carbon::parse($start)->diffInMilliseconds($stop, true);
        $reqs = $runtime > 0 ? number_format(1000 / $runtime, 2) : '∞';
        $time_str = ' [运行时间：' . number_format($runtime, 6) . 'ms][吞吐率：' . $reqs . 'req/ms]';
        $memory_use = number_format((memory_get_usage() - $startUsage) / 1024, 2);
        $memory_str = ' [内存消耗：' . $memory_use . 'kb]';
        $file_load = ' [文件加载：' . count(get_included_files()) . ']';
        return [
            'serverInfo' => $pdo->getAttribute(PDO::ATTR_SERVER_INFO),
            'connectionStatus' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
            'serverVersion' => 'mysql: ' . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
            'sqlArr' => IteLogFacades::getSqlList(),
            'timeStr' => $time_str,
            'memoryStr' => $memory_str,
            'fileLoad' => $file_load,
            'response' => $response->getContent() ?: '',
            'status' => $response->getStatusCode()
        ];
    }
}
