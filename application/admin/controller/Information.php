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

class Information extends Controller{
    
    /**
     * [数据概况]
     * 郭杨
     */    
    public function data_index(){     
        return view("data_index");
    }




    /**
     * [溯源分析]
     * 郭杨
     */    
    public function analytical_index(){     
        return view("analytical_index");
    }
    
 }