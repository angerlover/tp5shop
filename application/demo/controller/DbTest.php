<?php

namespace app\demo\controller;

use think\Controller;
use think\Request;
use app\demo\model\Category;
use think\Db;
use app\home\model\Cart;

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

    /**
     * 看一看模型和数据库的区别
     */
    public function db2()
    {
        $res1 = model('home/Cart')->getByMemberId(4); // 返回的是模型
        $res2 = db('cart')->getByMemberId(4); // 我操尴尬了，只能取到一个记录
        $res3 = db('cart')->where('member_id',4)->select();
        $cart = model('home/Cart');
        $cart::create(['member_id'=>5]);
        halt($res3);
    }


}
