<?php
/**
 * Created by PhpStorm.
 * User: pepe
 * Date: 2017/9/7 0007
 * Time: 10:18
 */

namespace app\test\controller;
use think\Controller;


class Goods extends Controller
{
    public function add()
    {
        $goodsModel = model('Goods');
        if($_POST)
        {
            if ($goodsModel->allowField(true)->validate(true)->save(input('post.')))
            {
                return '成功';
            }

            return $goodsModel->getError();
        }

        return view();
    }
}