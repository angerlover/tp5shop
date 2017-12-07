<?php

namespace app\home\controller;

use think\Controller;
use think\Request;

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
        $goods_attr_ids = $request->post()['goods_attr_id'];
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

    public function index()
    {
        // 取出购物车的数据
        if($userid = session('id'))
        {
            // 登录从数据库取
            $data = db('cart')->where('member_id',$userid)->select();
//            dump($data);die;
            $res = [];
            $totalPrice = null;
            foreach($data as $k=>$v)
            {
                $res[$v['id']]['logo'] = db('goods')->where('id',$v['goods_id'])->value('sm_logo');
                $res[$v['id']]['goods_name'] = db('goods')->where('id',$v['goods_id'])->value('goods_name');
                $res[$v['id']]['amount'] = $v['goods_number'];
                $res[$v['id']]['price'] = model('Member')->getPrice($v['goods_id']);
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
//            dump($res);die;
            $this->assign(['data'=>$res,'totalPrice'=>$totalPrice]);
        }
        else // 没登录从cookie中取
        {
            $data = isset($_COOKIE['cart'])?unserialize($_COOKIE['cart']):[];
//            var_dump($data);die;
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
                // 获取商品的价格（模型自定义的方法）
                $res[$k]['price'] = model('Member')->getPrice($goodsid);
                $totalPrice += $res[$k]['amount']*$res[$k]['price'];
                // 追加一个goodsid
                $res[$k]['goods_id'] = $goodsid;
            }
//            var_dump($res);die;
            $this->assign(['data'=>$res,'totalPrice'=>$totalPrice]);
        }
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
}
