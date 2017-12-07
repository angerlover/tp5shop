<?php
namespace app\home\controller;
use think\Controller;

class Index extends Nav
{
    /**
     * @return \think\response\View
     * 商城首页
     */
    public function index()
    {
        $catModel = model('admin/Category');
        // 获取推荐楼层的数据
        $floorData = $catModel->getFloorData();
        // 传值
//        var_dump($floorData);die;
        $this->assign(['floorData'=>$floorData,'isNav' => true]);

        // 渲染
        return view();
    }

    public function test()
    {
        $catModel = model('admin/Category');
        $data = $catModel->getChildren(1);
        $data = $catModel->getChildren(2);

        dump($data);die;
    }
}
