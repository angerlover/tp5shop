<?php

namespace app\home\controller;

use think\Controller;
use think\Request;

/**
 * Class Nav
 * @package app\home\controller
 * 带nac的基类控制器
 */
class Nav extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        // 1获取导航栏数据
        $catModel = model('admin/Category');

        // 使用redis做数据缓存
        // TODO:category数据变动如何改变？
        $redis = getRedis();
        if($redis->keys('navData'))
        {
            $navData  = unserialize($redis->get('navData'));
        }
        else
        {
            $navData = $catModel->getnavData();
            $redis->set('navData',serialize($navData));
        }
        // 赋值
        $this->assign(['navData'=>$navData]);
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
