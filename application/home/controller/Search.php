<?php

namespace app\home\controller;

use think\Controller;
use think\Request;

class Search extends Controller
{
    /**
     * 分类搜索页面
     *
     * @return \think\Response
     */
    public function cat_search()
    {
        // 接收参数
        $catId = request()->param('cat_id');
        // 获取当前分类下的品牌，价格区间和可选属性
        $condition = model('admin/Category')->getSearchConditonByCatId($catId);
//        halt($condition);
        // 面包屑导航
        $bread = model('admin/Category')->getParentPath($catId);
//        halt($bread);
        // 赋值
        $this->assign(['condition'=>$condition,
                        'bread' => $bread,
                    ]);
        return view('cat_search');

    }




}
