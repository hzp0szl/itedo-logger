<?php

namespace IteLog\Service;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class IteLogService
 * @package IteLog\Service
 */
class IteLogService
{
    /**
     * @var $sqlArr
     */
    protected $sqlArr;

    /**
     * @var string
     */
    protected $startTime;

    /**
     * @var
     */
    protected $exceptions = [];

    /**
     * 设置SQL列表
     *
     * @param $sqlArr
     */
    public function setSqlList($sqlArr)
    {
        $this->sqlArr = $sqlArr;
    }

    /**
     * 获取SQL列表
     *
     * @return mixed
     */
    public function getSqlList()
    {
        return $this->sqlArr;
    }

    /**
     *  设置开始时间
     */
    public function setStartTime()
    {
        $this->startTime = Carbon::now()->toDate()->format('Y-m-d H:i:s.u');
    }

    /**
     * 获取开始时间
     *
     * @return string
     */
    public function getStartTime(): string
    {
        return $this->startTime;
    }

    /**
     * 插入数据
     *
     * @param array $inData
     */
    public function into(array $inData)
    {
        $inData['exceptions'] = $this->getExceptions();
        $driver = config('itelog.driver');
        switch ($driver) {
            case 'mongodb':
                $this->intoMongodb($inData);
                break;
            case 'file':
                $this->intoFile($inData);
                break;
        }
    }

    /**
     * @param array $inData
     */
    private function intoMongodb(array $inData)
    {
        $collection = config('itelog.mongo_table', 'iteLog');
        $db = DB::connection('mongodb')->collection($collection);
        $db->insert($inData);
    }

    /**
     * @param array $inData
     */
    private function intoFile(array $inData)
    {
        Log::channel('iteLog')->info('', $inData);
    }

    /**
     * 设置错误日志
     *
     * @param array $exceptions
     */
    public function setExceptions(array $exceptions)
    {
        $this->exceptions = $exceptions;
    }

    /**
     * 获取错误日志
     *
     * @return mixed
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }
}
