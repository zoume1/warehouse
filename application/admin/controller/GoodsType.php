<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/25/025
 * Time: 14:13
 */

namespace app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;
use think\paginator\driver\Bootstrap;

class GoodsType extends Controller{


    /**
     * [商品分类列表显示]
     * GY
     */
    public function index($pid = 0)
    {
        $goods = [];
        $wares = db("wares") -> select();

        if($pid == 0)
        {
            $goods = getSelectList("wares");
        }
        
        foreach ($wares as $key => $value)
        {
            if ($value["pid"]) {
                $res = db("wares") -> where("id", $value['pid']) -> field("name") -> find();
                //halt($res);
                $wares[$key]["names"] = $res["name"];
            }
        }
        $all_idents = $wares;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $wares = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/GoodsType/index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $wares->appends($_GET);
        $this->assign('page', $wares->render());

        return view("goods_type_index",["wares" => $wares, "goods" => $goods]);

    }



    /**
     * [商品分类列表添加]
     * GY
     */
    public function add($pid = 0)
    {
        $goods_liste = [];       
        if ($pid == 0)
        {
            $goods_liste = getSelectList("wares");
        }

        return view("goods_type_add",["goods_liste" => $goods_liste]);
    }



    /**
     * [商品分类列表入库]
     * GY
     */
    public function save(Request $request)
    {

        if($request->isPost())
        {
            $data = $request -> param();

            $bool = db("wares") -> insert($data);
            if($bool){
                $this -> success("添加成功",url("admin/GoodsType/index"));
            }else{
                $this -> error("添加失败",url("admin/GoodsType/add"));
            }
        }
    }



    /**
     * [商品分类列表修改]
     * GY
     */
    public function edit($pid = 0, $id)
    {
        
        $goods_list = [];
        $category = db("wares") -> where("id", $id) -> select();

        if ($pid == 0) {
            $goods_list = getSelectList("wares");
        }
        
        return view("goods_type_edit", ["category" => $category, "goods_lists" => $goods_list]);
    }



    /**
     * [商品分类列表更新]
     * GY
     */
    public function updata(Request $request)
    {

        if($request -> isPost())
         {    
            $data = $request -> param();
            $bool = db("wares") -> where('id', $request->only(["id"])["id"]) -> update($data);
            if ($bool) {
                $this->success("编辑成功", url("admin/GoodsType/index"));
            } else {
                $this->error("编辑失败", url("admin/GoodsType/edit"));
            }
        }
    }


    /**
     * [商品分类列表删除]
     * GY
     */
    public function del($id)
    {
        $bool = db("wares") -> where("id",$id) -> delete();
        if($bool){
            $this -> success("删除成功",url("admin/GoodsType/index"));
        }else{
            $this -> error("删除失败",url("admin/GoodsType/edit"));
        }
    }


    /**
     * [商品分类列表ajax显示]
     * GY
     */
    public function ajax_add($pid = 0)
    {
        $goods_list = [];
        if($pid == 0){
            $goods_list = getSelectList("wares");
        }
        return ajax_success("获取成功",$goods_list);
    }




    /**
     * [商品分类列表批量删除]
     * 郭杨
     * @param int $pid
     * @return
     */
    public function dels(Request $request)
    {
        if ($request->isPost()) {
            $id = $_POST['id'];
            if (is_array($id)) {
                $where = 'id in(' . implode(',', $id) . ')';
            } else {
                $where = 'id=' . $id;
            }

            $list = Db::name('wares')->where($where)->delete();
            if ($list !== false) {
                return ajax_success('成功删除!', ['status' => 1]);
            } else {
                return ajax_error('删除失败', ['status' => 0]);
            }
        }
    }


    /**
     * [活动分类组模糊搜索]
     * 郭杨
     */
    public function search()
    {
        $ppd = input('ppd');          //商品分类
        $interest = input('interest'); //分类状态

        if ((!empty($ppd)) || (!empty($interest))) {
            $activ = db("wares")->where("pid", "like", "%" . $ppd . "%")->where("status", "like", "%" . $interest . "%")->select();
            foreach ($activ as $key => $value) {
                if ($value["pid"]) {
                    $res = db("wares")->where("id", $value['pid'])->field("name")->find();
                    $activ[$key]["names"] = $res["name"];
                }
            }
        } else {
            $activ = db("wares")->select();
            foreach ($activ as $key => $value) {
                if ($value["pid"]) {
                    $res = db("wares")->where("id", $value['pid'])->field("name")->find();
                    $activ[$key]["names"] = $res["name"];
                }
            }
        }
        $all_idents = $activ;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $activ = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/GoodsType/index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $activ->appends($_GET);
        $this->assign('page', $activ->render());
        return view('goods_type_index', ['wares' => $activ]);
    }


}

