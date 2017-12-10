<?php

namespace app\home\model;

use think\Model;

class Cart extends Model
{

    /**5
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

    /**
     * 获得购物车的数据
     */
    public function getCart()
    {

        // 登录从数据库取
        if($userid = session('id'))
        {
            $data = db('cart')->where('member_id',$userid)->order('id','desc')->select();
//            dump($data);die;
            $res = [];
            $totalPrice = null;
            foreach($data as $k=>$v)
            {
                $res[$v['id']]['logo'] = db('goods')->where('id',$v['goods_id'])->value('sm_logo');
                $res[$v['id']]['goods_name'] = db('goods')->where('id',$v['goods_id'])->value('goods_name');
                $res[$v['id']]['amount'] = $v['goods_number'];
                $res[$v['id']]['price'] = model('Member')->getPrice($v['goods_id']);
                if(empty($res[$v['id']]['price']))
                {
                    $res[$v['id']]['price'] = db('goods')->where('id',$v['goods_id'])->value('shop_price');
                }
                $goods_attr_ids = explode(',',$v['goods_attr_id']);
                $totalPrice += $res[$v['id']]['amount']*$res[$v['id']]['price'];
                $res[$v['id']]['goods_id'] = $v['goods_id'];
                foreach ($goods_attr_ids as $k1=>$v1)
                {
                    $res[$v['id']]['goods_attr'][] = db('goods_attr')->alias('a')
                        ->field('b.attr_name,a.attr_value')
                        ->join('__ATTRIBUTE__ b','a.attr_id = b.id')
                        ->where('a.id',$v1)
                        ->select();
                }
            }
            $cart['data'] = $res;
            $cart['price'] = $totalPrice;
            return $cart;
        }
        else // 没登录从cookie中取
        {
            $data = isset($_COOKIE['cart'])?unserialize($_COOKIE['cart']):[];
//            var_dump($data);die;
            // 倒叙
            $data = array_reverse($data,true);
            $res =  [];
            $totalPrice = 0;
            foreach($data as $k=>$v)
            {
                $temp = explode('-',$k);
                $goodsid = $temp[0];
                $goods_attr_id = $temp[1];
                $goods_attr_id = explode(',',$goods_attr_id);
                // 商品的属性
                foreach($goods_attr_id as $v1)
                {
                    $res[$k]['goods_attr'][] = db('goods_attr')->alias('a')
                        ->field('b.attr_name,a.attr_value')
                        ->join('__ATTRIBUTE__ b','a.attr_id = b.id')
                        ->where('a.id',$v1)
                        ->select();
                }
                // 商品logo
                $res[$k]['logo'] = db('goods')->where('id',$goodsid)->value('sm_logo');
                // 商品名称
                $res[$k]['goods_name'] = db('goods')->where('id',$goodsid)->value('goods_name');
                // 商品数量
                $res[$k]['amount'] = $v;
                // 获取商品的价格（模型自定义的方法）TODO 但是只能获取登录了会员的价格啊
                $res[$k]['price'] = model('Member')->getPrice($goodsid);
                if(empty($res[$k]['price']) || $res[$k]['price']==0)
                {
                    $res[$k]['price'] = db('goods')->where('id',$goodsid)->value('shop_price');
                }
                $totalPrice += $res[$k]['amount']*$res[$k]['price'];
                // 追加一个goodsid
                $res[$k]['goods_id'] = $goodsid;
            }
            $cart['data'] = $res;
            $cart['price'] = $totalPrice;
            return $cart;


        }
    }
}
