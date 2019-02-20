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

class Made extends Controller{
    
    /**
     * [专属定制]
     * 郭杨
     */    
    public function custom_made(){     
        return view("custom_made_index");
    }

    
 }