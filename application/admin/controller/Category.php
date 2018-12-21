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

class Category extends Controller
{


    /**
     * [活动分类显示]
     * 郭杨
     */
    public function index($pid = 0)
    {
        $goods = [];
        $category = db("goods_type")->select();

        if ($pid == 0) {
            $goods = getSelectList("goods_type");
        }
        foreach ($category as $key => $value) {
            if ($value["pid"]) {
                $res = db("goods_type")->where("id", $value['pid'])->field("name")->find();
                $category[$key]["names"] = $res["name"];
            }
        }
        $all_idents = $category;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页20行记录
        $showdata = array_slice($all_idents, ($curPage - 1) * $listRow, $listRow, true);// 数组中根据条件取出一段值，并返回
        $category = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path' => url('admin/Category/index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $category->appends($_GET);
        $this->assign('listpage', $category->render());

        return view("category_index", ["category" => $category, "goods" => $goods]);

    }



    /**
     * [活动分类添加]
     * 郭杨
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function add($pid = 0)
    {
        $goods_liste = [];
        $category = db("goods_type")->select();
        if ($pid == 0) {
            $goods_liste = getSelectList("goods_type");
        }
        return view("category_add", ["goods_liste" => $goods_liste]);

    }



    /**
     * [活动分类分组入库]
     * 郭杨
     */
    public function save(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->param();
            $show_images = $request->file("icon_image");

            if ($show_images) {
                $show_images = $request->file("icon_image")->move(ROOT_PATH . 'public' . DS . 'uploads');
                $data["icon_image"] = str_replace("\\", "/", $show_images->getSaveName());
            }

            $bool = db("goods_type")->insert($data);
            if ($bool) {
                $this->success("添加成功", url("admin/Category/index"));
            } else {
                $this->error("添加失败", url("admin/Category/add"));
            }
        }
    }



    /**
     * [活动分类分组修改]
     * 郭杨
     */
    public function edit($pid = 0, $id)
    {
        $goods_list = [];
        $category = db("goods_type")->where("id", $id)->select();
        if ($pid == 0) {
            $goods_list = getSelectList("goods_type");
        }

        return view("category_edit", ["category" => $category, "goods_lists" => $goods_list]);
    }



    /**
     * [图片删除]
     * 郭杨
     */
    public function images(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(['id'])['id'];
            $image_url = db("goods_type")->where("id", $id)->field("icon_image")->find();
            if ($image_url['icon_image'] != null) {
                unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $image_url['icon_image']);
            }
            $bool = db("goods_type")->where("id", $id)->field("icon_image")->update(["icon_image" => null]);
            if ($bool) {
                return ajax_success("删除成功");
            } else {
                return ajax_error("删除失败");
            }
        }
    }

    /**
     * [活动分类分组更新]
     * 郭杨
     * @param Request $request
     * @param $id
     */
    public function updata(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->param();
            $show_images = $request->file("icon_image");
            if ($show_images) {
                $show_images = $request->file("icon_image")->move(ROOT_PATH . 'public' . DS . 'uploads');
                $data["icon_image"] = str_replace("\\", "/", $show_images->getSaveName());
            }
            $bool = db("goods_type")->where('id', $request->only(["id"])["id"])->update($data);

            if ($bool) {
                $this->success("编辑成功", url("admin/Category/index"));
            } else {
                $this->error("编辑失败", url("admin/Category/edit"));
            }
        }
    }


    /**
     * [活动分类分组删除]
     * 郭杨
     */
    public function del($id)
    {
        $bool = db("goods_type")->where("id", $id)->delete();
        if ($bool) {
            $this->success("删除成功", url("admin/Category/index"));
        } else {
            $this->error("删除失败", url("admin/Category/index"));
        }
    }


    /**
     * [活动分类分组ajax显示]
     * 郭杨
     * @param int $pid
     * @return
     */
    public function ajax_add($pid = 0)
    {
        $goods_list = [];
        if ($pid == 0) {
            $goods_list = getSelectList("goods_type");
        }
        return ajax_success("获取成功", $goods_list);
    }

    /**
     * [活动分类分组批量删除]
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

            $list = Db::name('goods_type')->where($where)->delete();
            if ($list !== false) {
                return ajax_success('成功删除!', ['status' => 1]);
            } else {
                return ajax_error('删除失败', ['status' => 0]);
            }
        }
    }




    /**
     * [活动分类分组状态修改]
     * 郭杨
     */
    public function status(Request $request)
    {
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("goods_type")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Category/index"));
                } else {
                    $this->error("修改失败", url("admin/Category/index"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("goods_type")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Category/index"));
                } else {
                    $this->error("修改失败", url("admin/Category/index"));
                }
            }
        }
    }


    
    /**
     * [活动分类组模糊搜索]
     * 郭杨
     */
    public function search()
    {
        $ppd = input('ppd');          //活动分类
        $interest = input('interest'); //分类状态

        if ((!empty($ppd)) || (!empty($interest))) {
            $activ = db("goods_type")->where("pid", "like", "%" . $ppd . "%")->where("status", "like", "%" . $interest . "%")->select();
            foreach ($activ as $key => $value) {
                if ($value["pid"]) {
                    $res = db("goods_type")->where("id", $value['pid'])->field("name")->find();
                    $activ[$key]["names"] = $res["name"];
                }
            }
        } else {
            $activ = db("goods_type")->select();
            foreach ($activ as $key => $value) {
                if ($value["pid"]) {
                    $res = db("goods_type")->where("id", $value['pid'])->field("name")->find();
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
            'path' => url('admin/Category/index'),//这里根据需要修改url
            'query' => [],
            'fragment' => '',
        ]);
        $activ->appends($_GET);
        $this->assign('listpage', $activ->render());
        return view('category_index', ['category' => $activ]);
    }

}

