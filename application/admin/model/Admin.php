<?php
namespace app\admin\model;

use think\Model;

class Admin extends Model
{

    protected $table = "tb_admin";

    /**
     * [管理员查找]
     * @author 陈绪
     * @return \think\Paginator
     */
    public function sSelect(){
        return $this->paginate(10);
    }

    /**
     * [管理员入库]
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