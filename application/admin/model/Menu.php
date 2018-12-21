<?php
namespace app\admin\model;

use think\Model;

class Menu extends Model
{

    protected $table = "tb_menu";

    /**
     * [菜单查找]
     * @author 陈绪
     * @return \think\Paginator
     */
    public function sSelect(){
        return $this->order("sort_number")->paginate(10);
    }


    /**
     * [菜单添加]
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