<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/21 0021
 * Time: 14:22
 */
namespace  app\index\controller;

use think\Controller;
use think\Db;
use  think\Session;

class Diypage extends  Controller{
   public function index(){

   }

    public function moban(){
        $appletid = input("appletid");

        $res = Db::table('applet')->where("id",$appletid)->find();

        $usergroup = Session::get('usergroup');

        $this->assign('usergroup', $usergroup);


        if(!$res){

            $this->error("找不到对应的小程序！");
        }

        $this->assign('applet',$res);


        $is = Db::table("ims_sudu8_page_diypagetpl")->where('uniacid',$appletid)->select();

        //将原有页面放到一个模板中
        if(!$is){
            $pages = Db::table("ims_sudu8_page_diypage")->where('uniacid',$appletid)->field('id')->select();
            if($pages){
                $pageids = '';
                foreach ($pages as $key => $value) {
                    $pageids .= ','.$value['id'];
                }
                $pageids = substr($pageids,1);
                $data = [
                    'pageid' => $pageids,
                    'uniacid' => $appletid,
                    'template_name' => '原有页面模板',
                    'thumb' => "/diypage/img/blank.jpg",
                    'status' => 1,
                    'create_time' => time()
                ];
                Db::table("ims_sudu8_page_diypagetpl")->insert($data);
            }
        }
        $moban = Db::table("ims_sudu8_page_diypagetpl")->where('uniacid',$appletid)->select();
        foreach ($moban as $key => &$value) {
            $value['thumb'] = remote($appletid, $value['thumb'], 1);
        }
        $moban_sys = Db::table("ims_sudu8_page_diypagetpl_sys")->select();
        foreach ($moban_sys as $key => &$value) {
            $value['thumb'] = remote($appletid, $value['thumb'], 1);
        }
        $this->assign("moban_sys",$moban_sys);
        $this->assign("moban",$moban);
        return $this->fetch('moban');
    }
}