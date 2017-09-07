<?php
/**
 * Created by PhpStorm.
 * User: pepe
 * Date: 2017/9/6 0006
 * Time: 8:51
 */
namespace app\test\controller;
use think\Controller;
use think\Db;
use app\test\model\Goods;

class Db1 extends Controller
{
    /**
     * 测试Db类的数据库操作[插入]
     */
    function test1()
    {
//        $res = Db::execute('insert into p39_test (id,num) values (3,3); ');
//        dump($res);
//        Db::execute('insert into p39_test values(?,?)',[4,4]);
        Db::execute('insert into p39_test values(:id,:num)',['id'=>5,'num'=>5]);
    }

    /**
     * 测试Db类的数据库操作[查询]
     */
    function test2()
    {
        // 查询确定的字段
//          $res =  Db::name('test')->where('id','=','3')->select();
        // in
//          $res =  Db::name('test')->where('id','in',[1,2,3])->select();
        // between
//          $res =  Db::name('test')->where('id','between',[1,4])->select();
        // or 四个参数了 。。。
//        $res =  Db::name('test')
//            ->where('id',['>',1],['between',[1,4]],'or')
//            ->select();
        // 熟悉的和3.2一样的批量查询
//        $res = Db::name('test')
//                ->where([
//                    'id' => ['>',2],
//                    'num' => ['between',[1,100]]
//                ])->select();

        // 视图查询 不是真正的创建的视图 而是作用上相似 而且只能读
//        $res = Db::view('test','id,num')
//            ->where('id','>','2')
//            ->select();

        // 获取某行某列值 value函数
//        $res = Db::name('test')
//            ->where('id','=','4')
//            ->value('num');
//        $res = Db::name('test')
//            ->field('num')
//            ->where('id','=','4')
//            ->find();
        // 获取某一列的值
//        $res = Db::name('test')
//            ->where('id','>','2')
//            ->column('num');
        // 配对查询 id作为key num作为value
//        $res = Db::name('test')
//            ->where('id','>','2')
//            ->column('num','id');
//        $res = Db::name('test')
//            ->where('id','>','1')
//            ->column('*','id');
        // 聚合查询
//        $res = Db::name('test')
//            ->where('id','>','1')
//            ->count();

        // 占位符查询
//        $res = Db::name('test')
//            ->where('id > :id and num > :num',['id'=>3,'num'=>4])
//            ->select();

        // 日期查询
//        $res = Db::name('goods')
//            ->where('addtime > :addtime ',['addtime'=>'2017-8-28'])
//            ->select();
        // 分块查询
        Db::name('goods')
            ->where('id','>','1')
            ->chunk(2,function ($data){
                foreach ($data as $value)
                {
                    dump($value['goods_name']);
                    return true;
                }
            });

//        dump($res);
    }

    /**
     * 测试模型类
     */
    function test3()
    {
//        $res = Goods::get(2);
        // 插入操作1
        $goods = new Goods;
//        $goods->goods_name = '阿凡达';
//        $goods->shop_price = 123;
//        $res = $goods->save();
        // 插入操作2
//        $goodsAttr['goods_name'] = '小麦儿';
//        $goodsAttr['shop_price'] = 665;
//        if($res = Goods::create($goodsAttr))
//        {
//            echo '插入成功';
//        }

        // 批量插入
//        $list = [
//            ['goods_name'=>'嘻哈音乐','shop_price'=>666],
//            ['goods_name'=>'重金属音乐','shop_price'=>666],
//        ];
//        if($goods->saveAll($list))
//        {
//            echo '批量插入成功';
//        }
        /*********************查询操作***********************/
        // 查询基本都是静态方法

        // 神器动态变化的getXXX方法 实际是where goods_name =
//        $res = Goods::getByGoodsName('嘻哈音乐');

        // 条件查询
//        $res = Goods::get(['goods_name'=>'嘻哈音乐']);
//        $res = Goods::where('goods_name','like','%音乐%')->find();
        /********************更新**************************/
//        $res = Goods::get(2);
//        $res->goods_name = '我没钱';
//        if(false !== $res->save())
//        {
//            echo '更新成功';
//        }
        // 自定义更新
        $goodsAttr['goods_name'] = '我真的没钱';
        $goodsAttr['goods_desc'] = '我就操了你妈了';
        Goods::update($goodsAttr,['id'=>2]);

//        dump($res);
    }

    /**
     * 测试修改器
     */
    function test4()
    {
        $res = Goods::get(2);
        $res->summary = '很好';
        echo $res->summary;
    }

    /**
     * 测试自动完成和类型转换
     */
    function test5()
    {
//        $goods = Goods::get(2);
//        echo $goods->addtime; // 调用了自动类型转换功能
//        $goods->shop_price = 45;
//
//
        // 自动更新时间（配置文件打开了auto_timestamp）
        $goodsAttr['goods_name'] = '王云鹏';
        Goods::create($goodsAttr);
//        $goods->save();
    }
}