<?php
/**
 * User验证器
 * Created by PhpStorm.
 * User: pepe
 * Date: 2017/9/7 0007
 * Time: 10:16
 */
namespace app\test\validate;
use think\Validate;

class Goods extends Validate
{
    protected $rule = [
        ['goods_name','require','草泥马商品名称不能为空'],
        ['shop_price','require|number','价格不能为空 | 价格必须是数字'],
    ];
}