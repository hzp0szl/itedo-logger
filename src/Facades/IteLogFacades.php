<?php
namespace IteLog\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * 设置门面
 *
 * @method static setSqlList(array $sqlArr);
 * @method static getSqlList();
 * @method static setStartTime();
 * @method static getStartTime();
 * @method static into(array $inData);
 *
 * Class IteLogFacades
 * @package IteLog\Facades
 */
class IteLogFacades extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'iteLog';
    }
}
