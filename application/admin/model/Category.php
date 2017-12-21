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


    /**
     * 获取一个分类下所有的属性，价格区间，品牌
     */
    function getSearchConditonByCatId($catId)
    {
        $res = [];
        $where = [];
        $where['b.id'] = ['eq',$catId]; // 分类id
        // 首先获取分类所有商品id集合(二维数组)
        $temp = db('goods')->alias('a')
                ->field('a.id')
                ->join('__CATEGORY__ b','a.cat_id=b.id','left')
                ->where('a.cat_id',$catId)->select();
        // 二维转一维
        $ids = [];
        foreach($temp as $v)
        {
            $ids[] = $v['id'];
        }
//        halt($ids);
        // 存入where
        $where['a.id'] = ['in',$ids];
        // 大工程：根据传递过来的get参数来动态获取商品
        // 品牌
        if($brand = input('brand'))
        {
//            $brandId = db('brand')->where('brand_name',$brand)->value('id');
//            halt($brandId);
            $where['a.brand_id'] = $brand;
        }
        // 价格
        if($price = input('price'))
        {
            $temp = explode('-',$price);
            $where['a.shop_price'] = ['between',$temp];
        }
        // 属性：提取get参数的属性部分
        $attrParams = null; // 所有的属性筛选之后的id集合
        foreach(input('') as $k=>$v)
        {
            if(strpos($k,'attr_')===0)
            {
                $attrId = substr($k,strpos($k,'_')+1);
                $attrValue = mb_substr($v,mb_strpos($v,'-')+1);
                $tempGids = db('goods_attr')->field('goods_id')->where(['attr_id'=>$attrId,'attr_value'=>$attrValue])->select();
                // 如果确实有，改变一下数据结构之后存起来
                if($tempGids)
                {
                    $gids = [];
                    foreach ($tempGids as $v)
                    {
                        $gids[] = $v['goods_id'];
                    }
                    if($attrParams == null) // 第一次存
                    {
                        $attrParams = $gids;

                    }
                    else // 非第一次存,先交集一次
                    {
                        $attrParams = array_intersect($attrParams,$gids);
                        // 交集之后如果空了，也不需要再进行下一次遍历了
                        if(empty($attrParams))
                        {
                            $where['a.id'] = ['eq',0];
                            break;
                        }
                    }

                }
                else // 当前属性筛选没有id，直接给一个不可能满足的条件
                {
                    $where['a.id'] = ['eq',0];
                    break;
                }
            }
        }
//        halt($attrParams);
        // 遍历所有这些属性之后如果还有id集合，则存到where条件中
        if($attrParams)
        {
            $where['a.id'] = ['in',array_intersect($attrParams,$ids)];
        }
        $res['goods'] = db('goods')->alias('a')->field('a.sm_logo,a.goods_name,a.id,a.shop_price')
                        ->join('__CATEGORY__ b','a.cat_id = b.id','left')
                        ->join('__BRAND__ c','a.brand_id = c.id','left')
                        ->where($where)->select();
        // 获取可选属性
        $tempAttrs = db('goods_attr')->alias('a')->field('DISTINCT GROUP_CONCAT(a.attr_value) as value,b.attr_name,b.id')->join('__ATTRIBUTE__ b','a.attr_id=b.id','left')->where('a.goods_id','in',$ids)->where('b.attr_type','可选')->group('b.attr_name')->select();
//        halt($tempAttrs);
        // 改造属性的数据结构
        $attrs = [];
        foreach($tempAttrs as $k=>$v)
        {
            $attrs[$v['attr_name']] = array_merge(['id'=>$v['id']],['value'=>array_unique(explode(',',$v['value']))]);
        }
//        halt($attrs);
        $res['attr'] = $attrs;
        // 获取品牌
        $tempBrand = db('goods')->alias('a')->field('DISTINCT(b.brand_name),b.id')
                    ->join('__BRAND__ b','a.brand_id=b.id','left')
                    ->where('a.id','in',$ids)->select();
        // 改造品牌的数据结构
        $brand = [];
        foreach ($tempBrand as $k=>$v)
        {
            $brand[$v['id']] = $v['brand_name'];
        }
        $res['brand'] = $brand;
        /**********获取价格区间**********/
        // 商品数量
        $count = db('goods')->where('id','in',$ids)->count();
        // 最低价和最高价
        $lowPrice = db('goods')->min('shop_price');
        $highPrice = db('goods')->max('shop_price');
        $scope = $highPrice-$lowPrice;
        // 根据差值计算分段
        if($scope<=100)
        {
            $section = 2;
        }
        elseif($scope <200)
        {
            $section = 3;
        }
        elseif($scope <500)
        {
            $section = 5;
        }
        elseif($scope <1000)
        {
            $section = 7;
        }
        else
        {
            $section = 10;
        }
        $price = [];
        // 计算每段的范围
        $priceSection = ceil($scope/$section);
        $initPrice = 0;
        for($i=0;$i<$section;$i++)
        {
            // 计算每一段的结束价格
            $endPrice = $initPrice + $priceSection;
            // 取整
            $endPrice = ceil($endPrice);
            $price[$i] = $initPrice.'-'.$endPrice;

            // 计算下一个开始的价格
            $initPrice = $endPrice + 1;
        }
        $res['price'] = $price;


        return $res;
    }

}
