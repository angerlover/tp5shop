<?php
namespace app\home\controller;
use think\Controller;

class Index extends Controller
{
    /**
     * @return \think\response\View
     * 商城首页
     */
    public function index()
    {
        // 获取首页推荐楼层数据
        $catModel = model('admin/Category');
        $floorData = $catModel->getFloorData();
        var_dump($floorData);die;
    }
}
