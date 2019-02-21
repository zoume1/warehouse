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

class  Property extends  Controller{
    
    /**
     * [资产]
     * 郭杨
     */    
    public function property_index(){     
        return view("property_index");
    }

    
 }