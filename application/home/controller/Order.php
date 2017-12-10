<?php

namespace app\home\controller;

use think\Controller;
use think\Request;

class Order extends Controller
{
    /**
     * 保存一张订单
     */
    public function save()
    {
//        halt(input('post.'));
        $model = model('Order');
        if($model->allowField(true)->save(input('post.')))
        {
            return $this->success('下单成功','orderSuccess');
        }
        else
        {
            return $this->error($model->getError(),url('Cart/prepareForOrder'));
        }

    }


    public function orderSuccess()
    {
        return view();
    }
}
