<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/2/20
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;
use think\paginator\driver\Bootstrap;

class  General extends  Controller{
    
    /**
     * [店铺概况]
     * 郭杨
     */    
    public function general_index(){     
        return view("general_index");
    }

    
    /**
     * [小程序设置]
     * 郭杨
     */    
    public function small_routine_index(){     
        return view("small_routine_index");
    }


    /**
     * [小程序装修]
     * 郭杨
     */
    public function decoration_routine_index(){     
        $list = Db::name('pages')->paginate(10);
        return view("decoration_routine_index",['list'=>$list,'page'=>'']);
    }

    public function decoration_routine_details(){
        return view("decoration_routine_details");
    }
 }