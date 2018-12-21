<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
class Role extends Controller
{
    /**
     * [角色列表]
     * 陈绪
     */
    public function index(Request $request){
        $role_lists = db("role")->select();
        foreach($role_lists as $key=>$value){
            if($value["pid"]){
                $rs = db("role")->where("id",$value['pid'])->field("name")->find();
                $role_lists[$key]["parent_depart_name"] = $rs["name"];
            }
        }
        //halt($role_lists);
        return view("index",["role_lists"=>$role_lists]);
    }

    /**
     * [角色节点查询]
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function add(Request $request){
        $roles = db("role")->field("id,name")->select();
        $menu_list = db("menu")->where("status", "<>", 0)->select();
        $menu_lists = _tree_hTree(_tree_sort($menu_list, "sort_number"));
        return view("save",["roles"=>$roles,"menu_lists"=>$menu_lists]);
    }

    /**
     * [角色添加入库]
     * 陈绪
     */
    public function save(Request $request){
        $data = $request->only(["name","pid","status","desc"]);
        $data["menu_role_id"] = empty($request->only(["menu_role_id"])["menu_role_id"]) ? '' : implode(',', $request->only(["menu_role_id"])["menu_role_id"]);
        $boolData = db("role")->insert($data);
        if($boolData){
            $this->success("角色添加成功",url("admin/role/index"));
        }else{
            $this->error("添加角色失败",url("admin/role/add"));
        }
    }

    /**
     * [角色删除]
     * 陈绪
     */
    public function del($id){
        $bool = model("role")->where("id",$id)->delete();
        if($bool){
            $this->success("删除成功",url("admin/role/index"));
        }else{
            $this->error("删除失败",url("admin/role/index"));
        }
    }

    /**
     * [角色编辑]
     * 陈绪
     */
    public function edit($id){
        $roles = db("role")->where("id",$id)->select();
        $role_name = db("role")->where("id",$roles[0]["pid"])->field("name,id")->select();
        $menu_list = db("menu")->where("status","<>",0)->select();
        $menu_lists = _tree_hTree(_tree_sort($menu_list,"sort_number"));
        return view("edit",["roles"=>$roles,"menu_lists"=>$menu_lists,"role_name"=>$role_name]);
    }


    /**
     * [角色修改]
     * 陈绪
     */
    public function updata(Request $request,$id){
        $data = $request->only(["name","pid","status","desc"]);
        $data["menu_role_id"] = empty($request->only(["menu_role_id"])["menu_role_id"]) ? '' : implode(',', $request->only(["menu_role_id"])["menu_role_id"]);
        $boolData = db("role")->where("id",$id)->update($data);
        if($boolData){
            $this->success("角色修改成功",url("admin/role/index"));
        }else{
            $this->error("添加修改失败",url("admin/role/add"));
        }
    }


    /**
     * 角色状态修改
     * @param Request $request
     */
    public function status(Request $request){
        if($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("role")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/role/index"));
                } else {
                    $this->error("修改失败", url("admin/role/index"));
                }
            }
            if($status == 1){
                $id = $request->only(["id"])["id"];
                $bool = db("role")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/role/index"));
                } else {
                    $this->error("修改失败", url("admin/role/index"));
                }
            }
        }
    }


}