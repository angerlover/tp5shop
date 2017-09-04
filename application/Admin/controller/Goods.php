<?php
namespace app\Admin\controller;
use think\Controller;
class Goods extends Controller
{
    public function lst()
    {
        $userModel = new \app\Admin\model\Goods();
        $data = $userModel->select();
	    
        $this->assign('data',$data);

        return $this->fetch();
    }
}
