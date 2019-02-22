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

class StoreHouse extends Controller{
    
    /**
     * [仓库管理]
     * 郭杨
     */    
    public function stores_control(){     
        return view("store_house");
    }



    /**
     * [仓库管理]
     * 郭杨
     */    
    public function stores_divergence(){     
        return view("stores_divergence");
    }
    
 }