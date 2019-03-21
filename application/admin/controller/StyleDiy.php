<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/21 0021
 * Time: 9:34
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;
class StyleDiy extends  Controller{
    public function index()
    {
        $list =Db::table("applet")->paginate(20);
        return view("index",["list"=>$list]);
    }


}