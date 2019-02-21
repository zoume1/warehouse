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

class InterCalate extends Controller{
    
    /**
     * [通用模块]
     * 郭杨
     */    
    public function module_index(){     
        return view("module _index");
    }


    /**
     * [运营模块]
     * 郭杨
     */    
    public function business_index(){     
        return view("business_index");
    }

    /**
     * [配送设置]
     * 郭杨
     */    
    public function dispatching_index(){     
        return view("dispatching_index");
    }
    
 }