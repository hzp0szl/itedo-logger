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
     * @param $sqlArr
     */
    public function setSqlList($sqlArr)
    {
        $this->sqlArr = $sqlArr;
    }

    /**
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
     * @return string
     */
    public function getStartTime(): string
    {
        return $this->startTime;
    }

    public function into(array $inData)
    {
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
        dd($inData);
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
}
