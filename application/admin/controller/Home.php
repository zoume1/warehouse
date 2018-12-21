<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/26 0026
 * Time: 17:23
 */
namespace  app\admin\controller;

use think\Controller;
use think\Session;
use think\Config;

class Home extends Controller{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:后台首页
     **************************************
     * @return \think\response\View
     */
    public function index(){
        $admin = Config::get("admin");
        return view('index',["admin"=>$admin]);
    }


}