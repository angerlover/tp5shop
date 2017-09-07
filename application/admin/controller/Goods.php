<?php
namespace app\admin\controller;
use think\Controller;
use app\Admin\model\Goods as GoodsModel;
use think\Loader;
//use think\Request;

class Goods extends Controller
{
    /**
     * @return mixed
     *  商品列表页
     */
    public function lst()
    {
        $userModel = model('admin/Goods');
        $data = $userModel->order('id desc')->select();
	    
        $this->assign('data',$data);
//        return $this->fetch();
        return view();
    }

    /**
     * 添加商品
     */
    public function add()
    {
        if($_POST)
        {
            $goodsModel = model('test/Goods');
            $goodsModel->test();die;
            $data = input('post.');
            if($goodsModel->allowField(true)->validate(true)->save(input('post.')))
            {
                $this->success('添加成功',url('lst'));
            }

            $this->error('添加失败'.$goodsModel->getError());
        }
        return view();
    }

    function test()
    {
//        dump($this->request->url());
//        dump($this->request->param());
//        dump(input('get.name'));
//        echo url('',['name'=>2]);
//        dump(__ACTION__);
        $this->assign('data','hello');
         return view();
    }

}
