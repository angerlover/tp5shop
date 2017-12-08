<?php
namespace app\demo\controller;

use think\Request;
use think\Controller;

class Index //extends Controller
{
    public function index()
    {
        return request()->url();
    }

    /**
     * @param $name
     * @return mixed
     * 测试带参数的url 参数如果定义了就是必须的，如果url没有就会报错
     */
    public function getName($name='thinkphp')
    {
        return $name;
    }

    /**
     *
     */
    public function HelloWorld()
    {

    }

    /**
     * 测试url函数
     */
    public function testUrl()
    {
        return url('index/getName',['name'=>'王云鹏']);
    }

    /**
     * 测试request对象
     */
    public function testRequest(Request $request)
    {
        $request = Request::instance();
        return $request->url();
    }

    /**
     * 测试param
     */
    public function testParam()
    {
        $request = request();
        $params = input('name');
//        dump($request->param());
        dump($params);
    }

    /**
     * @return mixed
     * 测试input函数
     */
    public function testInput()
    {
    //  dump(input('get.')); 这个方法是获取不到的
//        dump(request()->get('name')); //也不行
//        dump(request()->param('name')); // 可行
    }

    /**
     * @param Request $request
     * 测试当前控制器方法等信息
     */
    public function testInfo(Request $request)
    {
        echo $request->action().'<br>'; // 仅仅只返回testInfo这个字符串，这有个瘠薄用啊
        echo $request->url(true).'<br>';
        echo 'pathinfo:'.$request->pathinfo().'<br>';
        echo 'routeInfo:    '.'<br>';
        dump($request->routeInfo());

    }




}
