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

/**
 * 去掉param参数后返回URL
 */
function parseUrl($param)
{
    // 获取当前的url
    $url = request()->url();
    $pattern = "/\/$param\/[^\/]+/";
    return preg_replace($pattern,'',$url);
}


