<?php

namespace app\demo\controller;

use think\Controller;
use think\Request;
use app\demo\model\Category;
use think\Db;

/**
 * Class DbTest
 * @package app\demo\controller
 * 完全就是测试Db的一个
 */
class DbTest extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $data = Category::all();
        dump($data[0]['cat_name']);
    }

    /**
     * 测试查询构造器
     */
    public function db1()
    {
        // 使用find的一定只返回一条
//        $res = Db::name('test')->where('num',3)->find();
        // select返回一个数组 如果没有则返回一个空数组
//        $res = Db::name('test')->where('num',3)->select();
//        dump($res);die;


        // 只查询一个字段的值（最小查询情况了吧）
        $num = Db::name('test')->where('id',3)->value('num');
        return $num;

    }
}
