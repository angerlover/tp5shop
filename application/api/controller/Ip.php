<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
require VENDOR_PATH.'ip/IP.class.php';
/**
 * Class Ip
 * @package app\api\controller
 * ip地址查询API
 */
class Ip extends Controller
{
    public function Test()
    {
        var_dump(\Ip::find('223.73.146.156'));
    }
}
