<?php

namespace IteLog\Service;

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
}
