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

class Crowd extends Controller{
    
    /**
     * [众筹商品]
     * 郭杨
     */    
    public function crowd_index(){     
        return view("crowd_index");
    }

    
 }