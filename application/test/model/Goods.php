<?php
/**
 * Created by PhpStorm.
 * User: pepe
 * Date: 2017/9/6 0006
 * Time: 21:47
 */

namespace app\test\model;
use think\Model;

class Goods extends Model
{
    protected $updateTime = 'addtime';
    protected $createTime = 'addtime';
    protected $update = [
        'goods_name',

    ];
    // 自动类型转换
    protected $type = [
        'addtime' => 'datetime:Y/m/d',
    ];

    /**
     * goods_name读取器
     * @param $goods_name
     * @param $data
     * @return string
     */
    function getGoodsNameAttr($goods_name)
    {
        return '这件垃圾的名字是'.$goods_name;
    }
    /**
     * goods_name修改器
     * @param $goods_name
     * @param $data
     * @return string
     */
    function setGoodsNameAttr($goods_name,$data)
    {
        return '大促销'.$goods_name;
    }

    /**
     * summary的修改器
     * @param $summary
     * @param $data
     * @return string
     */
    function setSummaryAttr($summary,$data)
    {
        return $summary.$data['shop_price'];
    }

    function test()
    {
        echo '这是test模块的model';
    }


}