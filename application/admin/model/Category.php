<?php

namespace app\admin\model;

use think\Model;

class Category extends Model
{
    /**
     * @param int $num
     * 获取首页的推荐楼层数据
     */
    function getFloorData($num = 3)
    {
        $res = [];
        // 获取全部数据
        $all = $this->select();
        // 遍历三次获取前三级的分类
        foreach ($all as $k=>$v)
        {
            $_v = $v;
            if($_v['parent_id']==0)
            {
                // 一级分类存
                // 继续寻找第二级
                foreach($all as $k1=>$v1)
                {
                    $_v1 = $v1;
                    if($_v1['parent_id']==$_v['id'])
                    {
                        // 继续寻找第三级的
                        foreach ($all as $k2=>$v2)
                        {
                            $_v2 = $v2;
                            if($_v2['parent_id']==$_v1['id'])
                            {
                                $_v1['children'][] = $_v2;
                            }
                        }
                        $_v['children'][] = $_v1;
                    }
                }
                // 一级分类入数组
                $res[] = $_v;

            }
        }

        return $res;

    }
}
