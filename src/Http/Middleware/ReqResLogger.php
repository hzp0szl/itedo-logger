<?php

namespace IteLog\Http\Middleware;

use Closure;
use Doctrine\DBAL\Driver\PDOConnection;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
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
        IteLogFacades::setStartTime();
        $start = IteLogFacades::getStartTime();
        $response = $next($request);
        $inData = [
            'startTime' => date('Y-m-d H:i:s.u', $start),
            'request' => $this->request($request),
            'authorization' => $request->server('HTTP_AUTHORIZATION'),
            'resource' => $this->resource($response),
        ];
        IteLogFacades::setStartTime();
        $stop = IteLogFacades::getStartTime();
        $inData['stopTime'] = date('Y-m-d H:i:s.u', $stop);
        $inData['time'] = $stop - $start;
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
     * @param $response
     * @return array
     */
    private function resource($response): array
    {
        $pdo = DB::getPdo();
        $runtime    = round(microtime(true) - microtime(true), 10);
        $reqs       = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';
        $time_str   = ' [运行时间：' . number_format($runtime, 6) . 's][吞吐率：' . $reqs . 'req/s]';
        $memory_use = number_format((memory_get_usage() - memory_get_usage()) / 1024, 2);
        $memory_str = ' [内存消耗：' . $memory_use . 'kb]';
        $file_load  = ' [文件加载：' . count(get_included_files()) . ']';
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
