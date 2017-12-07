<?php
/**
 * @return Redis
 * 获取
 */
function getRedis()
{
    static $redis;
    if(empty($redis))
    {
        $redis = new Redis();
        $redis->connect('localhost');
        return $redis;
    }
    else
    {
        return $redis;
    }
}

