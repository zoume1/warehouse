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

class Material extends Controller{
    
    /**
     * [防伪溯源]
     * 郭杨
     */    
    public function anti_fake(){     
        return view("anti_fake");
    }


    /**
     * [视频直播]
     * 郭杨
     */    
    public function direct_seeding(){     
        return view("direct_seeding");
    }

    /**
     * [温湿感应]
     * 郭杨
     */    
    public function interaction_index(){     
        return view("interaction_index");
    }
    
 }