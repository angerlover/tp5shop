<?php
/**
 * Created by PhpStorm.
 * User: pepe
 * Date: 2017/9/4 0004
 * Time: 14:43
 */

namespace app\Admin\validate;
use think\Validate;

class Goods extends Validate
{
    protected $rule = [
        'goods_name' => 'require',
        'shop_price' => 'number',
    ];

}