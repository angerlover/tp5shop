<?php
/**
 * Created by PhpStorm.
 * User: pepe
 * Date: 2017/12/6
 * Time: 21:16
 */

namespace app\home\validate;
use think\Validate;

/**
 * Class Member
 * @package app\home\validate
 * 模型验证类
 */
class Member extends Validate
{
    protected $rule = [
            'username' => 'require|max:15|unique:member',
            'password' =>'require|min:4|max:15',
            'password2' =>'require|confirm:password',
            'checkcode' => 'require|captcha'
                      ];

    // 定义场景
    protected $scene = [

            'login' => ['username'=>'require','password','checkcode'],

    ];

}