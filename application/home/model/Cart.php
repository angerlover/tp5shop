<?php

namespace app\home\model;

use think\Model;

class Cart extends Model
{

    /**
     * 检查库存量
     */
    public function checkGoodsNumber($goodsid,$number,$goods_attr_id)
    {
        // 把属性按照从小到达和后台统一排列，否则会出现库存量有但是检查不出来的情况
        sort($goods_attr_id,SORT_NUMERIC);
        // 把数组转换成字符串
        $ids = implode(',',$goods_attr_id);
//        dump($ids);die;
        $realNumber = db('goods_number')->where('goods_id',$goodsid)->where('goods_attr_id',$ids)->value('goods_number');
        return $realNumber>=$number?true:false;
    }
}
