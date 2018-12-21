<?php
namespace app\admin\model;

use think\Model;

class Role extends Model
{

    protected $table = "tb_role";


    /**
     * [角色查找]
     * @author 陈绪
     * @return \think\Paginator
     */
    public function sSelect(){
        return $this->paginate(10);
    }


    /**
     * [角色入库]
     * @author 陈绪
     * @param $arr
     * @return false|int
     */
    public function sSave($arr){
        if(is_array($arr)){
            return $this->save($arr);
        }
    }

}