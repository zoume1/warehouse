<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/8
 * Time: 15:21
 */
namespace app\index\controller;

use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\Image;


class TeaCenter extends Controller
{
    /**
     * [茶圈父级显示]
     * 郭杨
     */
    public function teacenter_data(Request $request)
    {
        if ($request->isPost()) {

            $tea = Db::name("goods_type")->field('name,icon_image,color,id')->where('pid', 0)->where("status", 1)->select();
            foreach($tea as $key => $value){
                $res = db("goods_type")->where("pid",$value['id'])->field("name,id")->find();
                $tea[$key]["tid"] = $res["id"];
                $tea[$key]["activity_name"] = $res["name"];
               
            }
            if (!empty($tea)) {
                return ajax_success('传输成功', $tea);
            } else {
                return ajax_error("数据为空");

            }


        }

    }




    /**
     * [茶圈子级显示]
     * 郭杨
     */
    public function teacenter_display(Request $request)
    {
        if ($request->isPost()){
            $id = $request->only(['id'])['id'];
            $resdata = Db::name("goods_type")->field('name,icon_image,color,id')->where('pid', $id)->where("status", 1)->select();
            
            if (!empty($resdata)) {
                return ajax_success('传输成功', $resdata);
            } else {
                return ajax_error("数据为空");

            }


        }


    }

    /**
     * [茶圈活动页面显示]
     * 郭杨
     */
    public function teacenter_activity(Request $request)
    {
        if ($request->isPost()){
            $res = $request->only(['id'])['id'];   
                    
            $activity = Db::name("teahost")->field('id,activity_name,classify_image,cost_moneny,start_time,commodity,label,marker,participats,address,pid')->where("label", 1)->where("pid",$res)->order("start_time")->select();
            if(empty($activity)){
                return ajax_error("下面没有活动");
            }
            foreach($activity as $key => $value){
                if($value["id"]){       
                    $rest = db("goods_type")->where("id", $res)->field("name,pid")->find();
                    $retsd = db("goods_type")->where("id",$rest["pid"])->field("name,color")->find();
                    $activity[$key]["names"] = $rest["name"];
                    $activity[$key]["named"] = $retsd["name"];
                    $activity[$key]["color"] = $retsd["color"];
                    $activity[$key]["start_time"] = date('Y-m-d H:i',$activity[$key]["start_time"]);
                }
            }
          
            if (!empty($activity)) {
                return ajax_success('传输成功', $activity);
            } else {
                return ajax_error("数据为空");

            }


        }


    }

     /**
     * [茶圈活动详细显示]
     * 郭杨
     */
    public function teacenter_detailed(Request $request)
    {
        if ($request->isPost()){
            $resd = $request->only(['id'])['id'];
            $actdata = Db::name("teahost")->field('id,activity_name,classify_image,cost_moneny,start_time,commodity,label,marker,participats,requirements,address,pid')->where("label", 1)->where("id",$resd)->select();
            
            foreach($actdata as $key => $value){
                $actdata[$key]["start_time"] = date('Y-m-d H:i',$actdata[$key]["start_time"]);
            }

            if (!empty($actdata)) {
                return ajax_success('传输成功', $actdata);
            } else {
                return ajax_error("数据为空");

            }


        }


    }

     /**
     * [茶圈所有活动]
     * 郭杨
     */
    public function teacenter_alls(Request $request)
    {
        if ($request->isPost()){
            $data = Db::name("teahost")->field('id,activity_name,classify_image,cost_moneny,start_time,commodity,label,marker,participats,requirements,address,pid')->where("label", 1)->order("start_time")->select();           
            foreach($data as $key => $value){
                if($value){
                    $rest = db("goods_type")->where("id", $value["pid"])->field("name,pid")->find();
                    $retsd = db("goods_type")->where("id",$rest["pid"])->field("name,color")->find();
                    $data[$key]["names"] = $rest["name"];
                    $data[$key]["named"] = $retsd["name"];
                    $data[$key]["color"] = $retsd["color"];
                    $data[$key]["start_time"] = date('Y-m-d H:i',$data[$key]["start_time"]);
                }
            }
           
            if (!empty($data)) {
                return ajax_success('传输成功', $data);
            } else {
                return ajax_error("数据为空");

            }


        }


    }


    /**
     * [茶圈首页推荐活动]
     * 郭杨
     */
    public function recommend(Request $request)
    {
        if ($request->isPost()){
            $data = Db::name("teahost")->field('id,activity_name,classify_image,cost_moneny,start_time,commodity,label,marker,participats,requirements,address,pid,status,open_request')->where("label", 1)->where('status',1)->order("start_time")->select();           
            foreach($data as $key => $value){
                if($value){
                    $rest = db("goods_type")->where("id", $value["pid"])->field("name,pid")->find();
                    $retsd = db("goods_type")->where("id",$rest["pid"])->field("name,color")->find();
                    $data[$key]["names"] = $rest["name"];
                    $data[$key]["named"] = $retsd["name"];
                    $data[$key]["color"] = $retsd["color"];
                    $data[$key]["start_time"] = date('Y-m-d H:i',$data[$key]["start_time"]);
                }
            }
           
            if (!empty($data)) {
                return ajax_success('传输成功', $data);
            } else {
                return ajax_error("数据为空");

            }


        }


    }
}