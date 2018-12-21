<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/3 0003
 * Time: 18:21
 */

namespace  app\admin\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;

class  Distribution extends  Controller{

    /**
     * [分销设置显示]
     * GY
     */
    public function setting_index()
    {
        $distribution = db("distribution") -> select();
        
        return view("setting_index",["distribution" =>$distribution ]);
    }



    /**
     * [分销设置编辑]
     * GY
     */
    public function setting_edit($id)
    {       
        $setting = db("distribution") -> where("id",$id) -> select();    
        return view("setting_edit",["setting" => $setting]);
    }




    /**
     * [分销设置编辑入库]
     * GY
     */
    public function setting_updata(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->param();
            $bool = db("distribution")->where('id', $request->only(["id"])["id"])->update($data);
            if ($bool) {
                $this->success("编辑成功", url("admin/Distribution/setting_index"));
            } else {
                $this->error("编辑失败", url("admin/Distribution/setting_index"));
            }
        }
    }




    /**
     * [分销商品页面]
     * GY
     */
    public function goods_index()
    {

        $commodity = db("commodity") -> paginate(20);
        return view('goods_index',["commodity"=> $commodity]);
    }



    /**
     * [分销商品添加商品]
     * GY
     */
    public function goods_add()
    {
        return view('goods_add');
    }




    /**
     * [分销商品编辑]
     * GY
     */
    public function goods_edit($id)
    {

        $goods = db("commodity") -> where("id",$id) ->select();       

        $goods[0]["grade"] = explode(",",$goods[0]["grade"]);
        $goods[0]["award"] = explode(",",$goods[0]["award"]);
        $goods[0]["scale"] = explode(",",$goods[0]["scale"]);
        $goods[0]["integral"] = explode(",",$goods[0]["integral"]);       
    
        return view('goods_edit',["goods"=> $goods]);
    }


    
    /**
     * [分销商品编辑更新]
     * GY
     */
    public function goods_update(Request $request)
    {

        if ($request->isPost()) {
            $goods_data = $request->param();

            $goods_data["rank"] = implode(",",$goods_data["rank"]);
            $goods_data["grade"] = implode(",",$goods_data["grade"]);
            $goods_data["award"] = implode(",",$goods_data["award"]);
            $goods_data["scale"] = implode(",",$goods_data["scale"]);
            $goods_data["integral"] = implode(",",$goods_data["integral"]);

            $bool = db("commodity")->where('id', $request->only(["id"])["id"])->update($goods_data);
            if ($bool) {
                $this->success("编辑成功", url("admin/Distribution/goods_index"));
            } else {
                $this->error("编辑失败", url("admin/Distribution/goods_edit"));
            }
        }
    }



    /**
     * [分销商品添加入库]
     * GY
     */
    public function goods_save(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->param();
            $top = $data["grade"];
            $res = array("zero" ,"first" ,"second","third");
            $des = db("goods") -> where("goods_number",$data["shop_number"])->field("goods_name,goods_show_images,goods_new_money")-> find();
            $deny = explode(",",$des["goods_show_images"]);
            $data["picture"] = $deny[0];
            $data["shop_name"] = $des["goods_name"];
            $data["shop_price"] = $des["goods_new_money"];

            foreach ($top as $key => $value)
            {
                $top[$key] = str_replace('%','',$value);
            }
            $array = array_combine($res,$top);

            foreach ($array as $k => $v)
            {
                $array[$k] = ($v * $des["goods_new_money"])/100;
            }
            $data["rank"] = implode(",",$data["rank"]);
            $data["grade"] = implode(",",$data["grade"]);
            $data["award"] = implode(",",$data["award"]);
            $data["scale"] = implode(",",$data["scale"]);
            $data["integral"] = implode(",",$data["integral"]);

            if(empty($data["shop_name"]))
            {
                $this->error("商品列表中没有该商品，请仔细核对后再添加", url("admin/Distribution/goods_add"));
            }

            $distribution = array_merge($data, $array);
            $bool = db("commodity")->insert($distribution);

            if ($bool) {
                $this->success("添加成功", url("admin/Distribution/goods_index"));
            } else {
                $this->error("添加失败", url("admin/Distribution/goods_add"));
            }
        }
    }


    /**
     * [分销商品组删除]
     * GY
     */
    public function goods_delete($id)
    {
        $bool = db("commodity")->where("id", $id)->delete();
        if ($bool) {
            $this->success("删除成功", url("admin/Distribution/goods_index"));
        } else {
            $this->error("删除失败", url("admin/Distribution/goods_index"));
        }

    }
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:分销记录页面
     **************************************
     * @return \think\response\View
     */
    public function record_index(){
        return view('record_index');
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:分销成员页面
     **************************************
     * @return \think\response\View
     */
    public function member_index(){
        return view('member_index');
    }



    /**
     **************李火生*******************
     * @param Request $request
     * Notes:分销成员页面编辑
     **************************************
     * @return \think\response\View
     */
    public function member_edit(){
        return view('member_edit');
    }





}