<?php
namespace app\Home\controller;
use think\Controller;

class Index extends Controller
{
    public function index($name = '草泥马')
    {
//    	$this->assign('name',$name);
    	return $this->fetch(
    		'index',[
    			'name' => $name,
    			'email' => 'haha'
    		]
    	);
    }
    
    
}
