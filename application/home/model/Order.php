<?php

namespace app\home\model;

use think\Model;

class Order extends Model
{
    protected $autoWriteTimestamp = false;
    protected static function init()
    {
        /***********下单之前的一系列检查(登录和库存)***********/
        Order::beforeInsert(function($order){
//            dump($order->toArray());die;
            // 检查是否登录
            if(!$userid = session('id'))
            {
                session('url',url('Order/save'));
                $order->error = '请先登录！';
                return false;
            }
            // 检查库存
            $goods =  unserialize($order->goods);
            foreach($goods as $k=>$v)
            {
                $goods_attr_id = []; // 商品属性id
                foreach($v['goods_attr'] as $k1=>$v1)
                {
                    $goods_attr_id[]= $k1;
                }
                $goods_attr_id = implode(',',$goods_attr_id);
                $realNumber = db('goods_number')->where('goods_id',$v['goods_id'])->where('goods_attr_id',$goods_attr_id)->value('goods_number');
                if($realNumber<$v['amount'])
                {
                    $order->error('库存不足!');
                    return false;
                }
            }

            // 下单的数据的拼凑
            $order->addtime = time();
            $order->pay_status = '否';
            $order->post_status = 0;
            $order->member_id = $userid;

            // 开启锁:把这个锁赋值给模型，可以到下单结束一直保持，否则函数执行完毕就没了。等到所有的订单逻辑结束了再释放锁
            $order->fp = fopen('./public/lock','r');
            flock($order->fp,LOCK_EX);

            // 开启事物
            $order->startTrans();
        });

        // 提交之后再继续插入订单商品表
        Order::afterInsert(function($order){

            // 循环goods数据插入到订单商品表中
            $goods = unserialize($order->goods);
            $order_id = $order->id;
//            halt($goods);
            foreach($goods as $k=>$v)
            {

                $goods_attr_id = []; // 商品属性id
                foreach($v['goods_attr'] as $k1=>$v1)
                {
                    $goods_attr_id[]= $k1;
                }
                $goods_attr_id = implode(',',$goods_attr_id);
                // 入库
                db('order_goods')
                    ->insert(['goods_id'=>$v['goods_id'],
                        'goods_number'=>$v['amount'],
                        'price'=>$v['price'],
                        'goods_attr_id' => $goods_attr_id,
                        'order_id' => $order_id
                    ]);
                // 减少库存
                db('goods_number')->where('goods_id',$v['goods_id'])->where('goods_attr_id',$goods_attr_id)->setDec('goods_number',$v['amount']);

                // 清空对应的购物车表中的数据
                db('cart')->where('id',$k)->where('member_id',session('id'))->delete();


            }
            // 提交事物
            $order->commit();
            // 释放锁
            flock($order->fp,LOCK_UN);

        });
    }
}
