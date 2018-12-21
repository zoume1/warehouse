<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
class Menu extends Controller
{
   protected $status = ["禁用","启用"];
    /**
     * [节点显示]
     * 陈绪
     */
    public function index(Request $request){
        $menu_lists = db("Menu")->order("sort_number")->paginate(20);
        $page = $menu_lists->render();
        return view("index",["menu_lists"=>$menu_lists,"page"=>$page]);
    }

    public function add(Request $request,$pid = 0){
        $menu_list = [];
        if ($pid == 0) {
            $menu_list = getSelectList("menu");
        }
        return view("save",["menu_list"=>$menu_list]);
    }

    /**
     * [节点添加入库]
     * 陈绪
     */
    public function save(Request $request){
       $data = $request->param();
       if(!empty($data)){
           $bool = model("Menu")->sSave($data);
           if($bool == true){
               $this->success("添加成功",url("admin/menu/index"));
           }else{
               $this->error("添加失败",url("admin/menu/add"));
           }
       }
    }

    /**
     * [菜单删除]
     * 陈绪
     * @param $id
     */
    public function del($id){
        $bool = model("Menu")->where("id",$id)->delete();
        if($bool){
            $this->success("删除成功",url("admin/menu/index"));
        }else{
            $this->error("删除失败",url("admin/menu/index"));
        }
    }

    /**
     * [菜单编辑]
     * 陈绪
     */
    public function edit($pid=0,$id){
        $menu = db("Menu")->where("id","$id")->select();
        $parent_cate = [];
        $menu_list = [];
        if($pid == 0){
            $menu_list = getSelectList("menu");
        }else{
            $parent_cate = model("Menu")->where("id",$pid)->field()->select();
        }
        return view("edit",["menu"=>$menu,"menu_list"=>$menu_list,"parent_cate"=>$parent_cate]);
    }

    /**
     * [菜单更新入库]
     * 陈绪
     */
    public function updata(Request $request,$id){
        $data = $request->param();
        $bool = db("Menu")->where('id',$id)->update($data);
        if ($bool){
            $this->success("编辑成功",url("admin/admin/index"));
        }else{
            $this->error("编辑失败",url("admin/admin/edit"));
        }
    }

}