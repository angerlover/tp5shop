<?php

namespace app\home\controller;

use think\Controller;
use think\Request;
use think\View;

/**
 * 购物车控制器
 */
class Cart extends Controller
{
    /**
     * 接受详情页传递过来的数据,检查库存,然后保存数据
     */
    public function save()
    {
        $request = request();
//        dump($request->param());die;
        // 如果商品有可选属性，就获取；如果没有就设置成空数组
        $goods_attr_ids = isset($request->post()['goods_attr_id'])?$request->post()['goods_attr_id']:[];
        $goods_id = $request->param('goods_id');
        $amount = $request->param('amount');
//        var_dump($goods_attr_ids);die;
        $ids = [];
        foreach($goods_attr_ids as $v)
        {
            $ids[] = $v;
        }
//        dump($ids);die;
        // 首先检查库存，如果库存不足则跳回商品详情页
        if(!model('Cart')->checkGoodsNumber($goods_id,$amount,$ids))
        {
            return $this->error('库存量不足！',url('Good/index',['id'=>$goods_id]));
        }
        // 判断登录状态
//        如果登录了存在数据库中
        if($userid = session('id'))
        {
            // 如果数据库中有这个商品属性，则增加数量即可
            sort($ids,1);
            if($has = db('cart')->where('goods_id',$goods_id)->where('goods_attr_id',implode(',',$ids))->where('member_id',$userid)->find())
            {
                db('cart')->where('goods_id',$goods_id)->where('goods_attr_id',implode(',',$ids))->where('member_id',$userid)->setInc('goods_number',$amount);
            }
            else
            {
                db('cart')->insert(['member_id'=>$userid,'goods_id'=>$goods_id,'goods_number'=>$amount,'goods_attr_id'=>implode(',',$ids)]);

            }
        }
        else // 没登陆存入cookie
        {
            $data = isset($_COOKIE['cart'])?unserialize($_COOKIE['cart']):[];
            // 拼装一个
            $key = "$goods_id".'-'.implode(',',$goods_attr_ids);
            $value = $amount;
            // 如果购物车有，则增加购买数量
            if(array_key_exists($key,$data))
            {
                $data[$key] += $value;
            }
            else // 如果购物车没有，则添加进去
            {
                $data[$key] = $value;
            }

            cookie('cart',serialize($data));
        }

        // 跳转到购物车的页面
        return $this->redirect('Cart/index');

    }

    /**
     * 购物车页面
     */
    public function index()
    {
        // 取出购物车的数据
        $data = model('Cart')->getCart();
        $this->assign(['data'=>$data['data'],'totalPrice'=>$data['price']]);
        return view();
    }

    /**
     * 删除购物车
     */
    public function delete()
    {
        $key = request()->param('id');
//        dump($key);die;
        // 验证登录情况
        if($userid = session('id'))
        {
            db('cart')->delete($key);
        }
        else // 没登录就删除cookie
        {
            // 取出cookie的中的内容
            $data = unserialize(cookie('cart'));
            // 删除
            $data = array_diff($data,[$data[$key]]);
            // 存回
            cookie('cart',serialize($data));

        }

        return redirect('Cart/index');

    }


    /**
     * 购物车点击结算后，最后一次清算购物车的数据并且显示确认订单页面
     */
    public function prepareForOrder()
    {
        /***依然分两种情况，未登录先把最新修改的购物车数据存入cookie中，然后再取出最新的购物车***/

        // 登录之后则修改相应的数据库后进入确认订单页面，先改再取的过程
        if($userid = session('id'))
        {
            $data = request()->post();
            // ①如果有提交过来的数据，说明是本身已经登录了，需要修改购物车表的数据
            if($data)
            {
                // 修改数据库
                foreach($data['key'] as $k=>$v)
                {
                    db('cart')->where('id',$v)->update(['goods_number'=>$data['amount'][$k]]);
                }

                // ②TODO 再取出刚刚修改过的数据,正式进入确认订单页面
                $goData = db('cart')->where('member_id',$userid)->where('id','in',$data['key'])->select();
                $res = [];
                $count = 0;
                $totalPrice = null; // 订单总金额
                foreach($goData as $k=>$v)
                {
                    $res[$v['id']]['logo'] = db('goods')->where('id',$v['goods_id'])->value('sm_logo');
                    $res[$v['id']]['goods_name'] = db('goods')->where('id',$v['goods_id'])->value('goods_name');
                    $res[$v['id']]['amount'] = $v['goods_number'];
                    $count += $res[$v['id']]['amount'];
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
                        $res[$v['id']]['goods_attr'][$v1] = db('goods_attr')->alias('a')
                            ->field('b.attr_name,a.attr_value')
                            ->join('__ATTRIBUTE__ b','a.attr_id = b.id')
                            ->where('a.id',$v1)
                            ->select();
                    }
                }
                $this->assign(['data'=>$res,'totalPrice'=>$totalPrice,'count'=>$count]);
                return view();
            }


        }
        else // 没有登录则把cookie中的数据更新一遍，提示登录，登录后跳转回来
        {

            $url = request()->url();//TODO 看看这个到底对不对,是对的
//            halt(url($url,['id'=>123],false));
            session('url',$url);
            $newData = request()->post();
            $data = unserialize(cookie('cart'));
            // 根据传来的参数，修改原data数据
            foreach($newData['key'] as $k=>$v) // 这个是key是cookie中的key，形如105-160,162,164,
            {
                $data[$v] = $newData['amount'][$k];
            }
            // 把修改好的值重新写入cookie
            cookie('cart',serialize($data));
            // 提示用户登录，然后跳转回来就可以直接到确认订单页面了
            return $this->error('请登录后再操作！',url('Member/login'));

        }
    }

    /**
     * ajax获取购物车数据
     */
    public function ajaxGetCart()
    {
        $data = model('Cart')->getCart();
        echo json_encode($data);
    }


}
