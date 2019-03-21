<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19 0019
 * Time: 14:18
 */
namespace  app\admin\controller;


use think\Controller;
use think\Request;

class  Upload extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:后台小程序上传图片
     **************************************
     */
    public function img_upload(Request $request){
        if($request->isPost()){
          $data =$request->file("file");
          halt($data);
        }
    }


}