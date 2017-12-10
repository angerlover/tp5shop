<?php

namespace app\admin\model;

use think\Model;
use think\Db;

class Category extends Model
{
    /**
     * @param int $num
     * 获取首页的导航栏数据
     */
    function getnavData($num = 3)
    {
        $res = [];
        // 获取全部数据
        $all = $this->select();
        $all = Db::name('Category')->select();
        // 遍历三次获取前三级的分类
        foreach ($all as $k=>$v)
        {
//            $v = $v->toArray();
            if($v['parent_id']==0)
            {
                // 一级分类存
                // 继续寻找第二级
                foreach($all as $k1=>$v1)
                {
//                    $v1 = $v1->toArray();
                    if($v1['parent_id']==$v['id'])
                    {
                        // 继续寻找第三级的
                        foreach ($all as $k2=>$v2)
                        {
//                            $v2 = $v2->toArray();
                            if($v2['parent_id']==$v1['id'])
                            {
                                $v1['children'][] = $v2;
                            }
                        }
                        $v['children'][] = $v1;
                    }
                }
                // 一级分类入数组
                $res[] = $v;
            }
        }

        return $res;

    }

    /**
     * @param $catId
     * 获取当前分类下所有商品（包含扩展分类）
     */
    function getGoodsByCatId($catId)
    {
        $res = [];
        $goodsModel = model('admin/Goods');
        // 主分类
        $mainData = $goodsModel
                    ->alias('a')
                    ->field('a.id')
                    ->join('__CATEGORY__ b','a.cat_id = b.id ')
                    ->where('a.cat_id',$catId)
                    ->select();
        // 扩展分类
        $extData = Db::name('goods_cat')->field('goods_id as id')->where('cat_id',$catId)->select();

        // 交集
        if($mainData && $extData)
        {
            $mainData = array_merge($mainData,$extData);
        }
        elseif ($extData)
        {
            $mainData = $extData;
        }

        // 二维变一维 纯数字id数组
        $res = [];
        foreach ($mainData as $v)
        {
            $res[] = $v['id'];
        }
        return $res;
    }



    function getFloorData()
    {
        $res = [];
        // 获取顶级分类下的推荐分类
        $topCat = db('category')->where('is_floor','是')->where('parent_id',0)->select();
//        var_dump($topCat);die;
        // 为每个顶级分类拼装一个没有推荐的子分类
        foreach($topCat as $k=>$v)
        {
            // 寻找当前的二级分类
            $children = db('category')->where('parent_id',$v['id'])->select();
            $tempUnFloor = $tempFloor = [];
            foreach($children as $v1)
            {
                if($v1['is_floor'] == '否')
                {
                    $tempUnFloor[] = $v1;
                }
                else
                {
                    // 取出推荐的二级分类的8个商品
                    $_children = array_slice($this->getGoodsByCatId($v1['id']),0,8);
                    $_temp = [];
                    foreach ($_children as $child)
                    {
                        $_temp[] = db('goods')->where('id',$child)->find();
                    }
                    $tempFloor[] = array_merge($v1,['goods'=>$_temp]);
                }
            }
//            var_dump($tempFloor);die;
            // 每层楼的品牌
            $brandIds = [];
            foreach ($tempFloor as $v1)
            {
                foreach($v1['goods'] as $v2)
                {
                    $brandIds[] = $v2['brand_id'];
                }
            }
            $brand = db('brand')->where('id','in',$brandIds)->select();
            $res[$k] = array_merge($v,['unFloor'=>$tempUnFloor,'floor'=>$tempFloor,'brand'=>$brand]);
        }

        return $res;
    }

    /**
     * @param $catId
     * 获取当前分类的所有子分类
     */
    function getChildren($catId,$isClear = true)
    {
        static $res = [];
        if($isClear)
        {
            $res = [];
        }

        $all = Db::name('category')->select();
        foreach($all as $v)
        {
            if($v['parent_id'] == $catId)
            {
                $res[] = $v;
                $this->getChildren($v['id'],false); // 继续寻找当前分类的子分类
            }
        }

        return $res;
    }


    /**
     * 寻找一个分类的所有父类（面包屑导航）
     */
    function getParentPath($catId)
    {
        static $res = [];
        $info = $this->field('id,cat_name,parent_id')->where('id',$catId)->find()->toArray();
        $res[] = $info;
        if($info['parent_id']>0)
        {
            $this->getParentPath($info['parent_id']);
        }

        return $res;
    }

}
