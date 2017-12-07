<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
require VENDOR_PATH.'sms/smsapi.class.php';

class Sms extends Controller
{
    /**
     * 发送测试
     */
    function test()
    {
        //接口账号
        $uid = 'pepe';

        //登录密码
        $pwd = 'kiss890825';

        /**
         * 实例化接口
         *
         * @param string $uid 接口账号
         * @param string $pwd 接口密码
         */
        $api = new \SmsApi($uid,$pwd);


        /*
        * 变量模板发送示例
        * 模板内容：您的验证码是：{$code}，对用户{$username}操作绑定手机号，有效期为5分钟。如非本人操作，可不用理会。【云信】
        * 变量模板ID：100003
        */

        //发送的手机 多个号码用,英文逗号隔开

        $mobile = '13192238571';

        //短信内容参数
        $contentParam = array(
            'code'		=> 571571,
            'username'	=> '小甜草莓'
        );

        //变量模板ID
        $template = '100006';

        //发送变量模板短信
        $result = $api->send($mobile,$contentParam,$template);

        if($result['stat']=='100')
        {
            return '发送成功';
        }
        else
        {
            return '发送失败:'.$result['stat'].'('.$result['message'].')';
        }
        //当前请求返回的原始信息
        //echo $api->getResult();

        //取剩余短信条数
        //print_r($api->getNumber());

        //获取发送状态
        //print_r($api->getStatus());

        //接收上行短信（回复）
        //print_r($api->getReply());


        /*******************发送之后一般要把成功的状态保存到一个专门的表中，对账用********/
    }
}
