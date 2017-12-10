<?php

namespace app\home\model;

use think\Model;

/**
 * Class Member
 * @package app\home\model
 * 会员模型
 */
class Member extends Model
{
    /**
     * 钩子函数
     */
    protected static function init()
    {
        Member::beforeInsert(function ($member)
        {
            $member->password = md5($member->password);
        });
    }

    /**
     * 验证登录
     */
    public function login()
    {
        $username = input('post.username');
        $password = input('post.password');
        // 检查数据库中是否存在
        $user = $this->where('username',$username)->find();
        if(!$user)
        {
            $this->error = '用户名不存在';
            return false;
        }
        else
        {
            // 检查密码是否正确
            $realpassword = $this->where('username',$username)->value('password');
            $id = $this->where('username',$username)->value('id');
            if(md5($password) == $realpassword) // 登录成功
            {
                // 存入session
                session('id',$id);
                session('username',$username);
                // 把cookie中的购物车数据存入数据库中,并清空cookie
                if(isset($_COOKIE['cart']))
                {
                    // 先处理原来的cookie数据
                    $data = unserialize($_COOKIE['cart']);
                    foreach($data as $k=>$v)
                    {
                        $temp = explode('-',$k);
                        $goods_id = $temp[0];
                        $goods_attr_ids = $temp[1];
                        $ids = explode(',',$goods_attr_ids);
                        sort($ids,1);
                        $final = implode(',',$ids);
                        // 如果表中已经有这条记录则直接修改数量
                        if(db('cart')->where(['member_id'=>$id,'goods_id'=>$goods_id,'goods_attr_id'=>$final])->find())
                        {
                            db('cart')->where(['member_id'=>$id,'goods_id'=>$goods_id,'goods_attr_id'=>$final])->setInc('goods_number',$v);
                        }
                        else
                        {
                            // 表中没有这条记录则添加
                            db('cart')->insert(['member_id'=>$id,'goods_id'=>$goods_id,'goods_number'=>$v,'goods_attr_id'=>$final]);
                        }
                    }
                    // 清空cookie的购物车数据
                    cookie('cart',null);

                }
                // 取出把登录之前要提交订单的购物车数据另存到session中
                if(isset($_COOKIE['toBuy']))
                {
                    $toBuy = unserialize($_COOKIE['toBuy']);
                    session('toBuy',$toBuy);
                    // 清空
                    cookie('toBuy',null);

                }
                return true;
            }
            else
            {
                $this->error = '密码不正确';
                return false;
            }
        }
    }

    /**
     * 在下单之前获取当前商品的价格（登录和未登录）
     */
    function getPrice($goodsid)
    {
        // 获取这个商品此时的促销价格
        $promotePrice = db('goods')->where('id',$goodsid)->value('promote_price');
        $shopPrice = db('goods')->where('id',$goodsid)->value('shop_price');
        // 登录则获取会员价格
        if($userid = session('id'))
        {
            $memberPrice = $this->getMemberPrice($userid,$goodsid);
            return $memberPrice<$promotePrice?$memberPrice:$promotePrice;
        }
        else
        {
            return $shopPrice<$promotePrice?$shopPrice:$promotePrice;
        }
    }

    /**
     * 获取会员价格
     */
    function getMemberPrice($id,$goodsid)
    {
        // 获取当前会员的积分
        $jifen = db('member')->where('id',$id)->value('jifen');
        // 获取当前的会员等级id
        $memberLevel = db('member_level')->where('jifen_bottom','<=',$jifen)->where('jifen_top','>',$jifen)->value('id');
        // 获取当前的价格
        $price = db('member_price')->where(['goods_id'=>$goodsid,'level_id'=>$memberLevel])->value('price');

        return $price;
    }


    /**
     *
     * 登出
     *
     */
    
    function logout()
    {
        session(null);
    }
}
