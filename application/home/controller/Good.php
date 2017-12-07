<?php

namespace app\home\controller;

use think\Controller;
use think\Request;
use think\Db;

/**
 * Class Good
 * @package app\home\controller
 * 商品详情页
 */
class Good extends Nav
{
    /**
     * 当前商品的详情信息
     *
     * @return \think\Response
     */
    public function index()
    {
        $goodsid = request()->param('id');
        $info = db('goods')->where('id',$goodsid)->find();
        // 获取会员价格
        $memberPrice = db('member_price')->alias('a')
                        ->join('__MEMBER_LEVEL__ b','a.level_id = b.id','LEFT')->where('a.goods_id',$goodsid)->select();
        // 获取商品相册
        $pics = db('goods_pic')->where('goods_id',$goodsid)->select();
        // 获取商品的可选属性
        $goodAttrs =  db('goods_attr')->alias('a')
                        ->field('a.attr_value,b.attr_name,a.id')
                        ->join('__ATTRIBUTE__ b','a.attr_id=b.id')
                        ->where('a.goods_id',$goodsid)
                        ->where('b.attr_type','可选')
                        ->select();
        // 技巧：按照属性名称分组
        $_goodAttrs = [];
        foreach($goodAttrs as $k=>$v)
        {
            $_goodAttrs[$v['attr_name']][] = array_merge([$v['attr_value'],$v['id']]);
        }
//        dump($_goodAttrs);die;
        // 获取商品的唯一属性
        $goodsUniquAttrs = db('goods_attr')->alias('a')
            ->field('a.attr_value,b.attr_name')
            ->join('__ATTRIBUTE__ b','a.attr_id=b.id')
            ->where('a.goods_id',$goodsid)
            ->where('b.attr_type','唯一')
            ->select();
        // 技巧：按照属性名称分组
        $_goodsUniqueAttrs = [];
        foreach($goodsUniquAttrs as $v)
        {
            $_goodsUniqueAttrs[$v['attr_name']] = $v['attr_value'];
        }
        // 统一的赋值
        $this->assign(['memberPrice'=>$memberPrice,
            'info' => $info,
            'pics' => $pics,
            'goodsAttr' => $_goodAttrs,
            'goodsUniqueAttr' => $_goodsUniqueAttrs,
            'isNav' => false,
        ]);

        return view();
    }

    /**
     * ajax接受历史数据
     */
    public function AjaxHistory()
    {
        $goodsId = request()->param('goods_id');
        $data = isset($_COOKIE['history']) ? unserialize($_COOKIE['history']):[];
        array_unshift($data,$goodsId);
//        var_dump($data);
        // 去重
        $data = array_unique($data);
        if(count($data)>5)
        {
            array_slice($data,0,5);// 只保留5个
        }
        // 装入cookie
        setcookie('history',serialize($data),time()+ 30 * 86400,'/');
        $temp = implode(',',$data);
        $gData = [];
        foreach ($data as $k=>$v)
        {
            $gData[] =  db('goods')->field('id,sm_logo,goods_name')->where('id',$v)->find();
        }
        // 返回
        echo json_encode(
            $gData
        );
    }
}
