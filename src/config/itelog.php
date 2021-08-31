<?php

return [
    //是否开启true false
    'logger' => 'true',
    //异常开启true false
    'exception' => 'true',
    //驱动 mongodb | file | mysql（后续完善）
    'driver' => 'mongodb',
    //driver是mongodb 时 需要填写表名
    'mongo_table' => 'ite_logger'
];
