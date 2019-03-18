<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/2/20
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;

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
        $list = Db::name('pages')->select();
        return view("decoration_routine_index",['list'=>$list,'page'=>'']);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:小程序编辑页面
     **************************************
     * @return \think\response\View
     */
    public function decoration_routine_details($id =null){
        if($id>0){
//            if(request()->isPost()){
                //            //点击编辑
//                $Pages = model('Pages');
//                $Pages->getOne($id);
//                echo json_encode(['errno'=>'','errstr'=>'','data'=>$Pages->data]);
//            }

        }
        return view("decoration_routine_details");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:
     **************************************
     * @return \think\response\View
     */
    public function delPages(){
        return view("decoration_routine_details");
    }
 }