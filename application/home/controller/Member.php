<?php

namespace app\home\controller;

use think\Controller;
use think\Request;
use think\captcha\Captcha;
use think\Validate;

/**
 * Class Member
 * @package app\home\controller
 * 会员
 */
class Member extends Controller
{
    /**
     * 登录
     */
    function login()
    {
        return view();
    }

    /**
     * 登录行为
     */
    function doLogin()
    {

        $memberModel = model('Member');
        // 登录表单验证： 此处Member.login其中Member是验证器的类名
        if($memberModel->allowField(true)->validate('Member.login'))
        {
            if($memberModel->login()) // 登录检查
            {
                // 登录成功跳转到登录之前的页面
                $url = '/';
                if(session('url'))
                {
                    $url = session('url');
                }
                return $this->success('登录成功',$url);
            }
            else
            {
                return $this->error($memberModel->getError(),url('login'));
            }
        }
        else
        {
            return $this->error($memberModel->getError(),url('login'));
        }


    }


    /**
     * 注册
     */
    function register()
    {
        return view();
    }

    /**
     * 注册行为
     */
    function doRegister()
    {
        $memberModel = model('Member');
        if($memberModel->allowField(true)->validate(true)->save(input('post.')))
        {
            return $this->success('注册成功',url('login'));
        }
        else
        {
            return $this->error($memberModel->getError(),url('register'));
        }
    }

    /**
     * 生成验证码
     */
    function captcha()
    {
        $captcha = new Captcha(['length'=>2]);

        return $captcha->entry();

    }

    /* 检测是否登录 */
    
    function isLogin()
    {
        if(session('id'))
        {
            echo json_encode(['error'=>0,'username'=>session('username')]);
        }
        else
        {
            echo json_encode(['error'=>1]);
        }
    }
    /**
     *
     * 登出
     *
     */
    
    function logout()
    {
        model('Member')->logout();
        return redirect('/');
    }

    /**
     * 获取当前会员的商品价格
     */
    function getMemberPrice()
    {
        $id = request()->param('id');
        $goodsid = request()->param('goodsid');
        $price = model('Member')->getMemberPrice($id,$goodsid);
        echo json_encode(['price'=>$price]);

    }
}
