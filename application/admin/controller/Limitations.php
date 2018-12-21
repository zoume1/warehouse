<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/3 0003
 * Time: 18:21
 */

namespace  app\admin\controller;

use think\Controller;

class  Limitations extends  Controller{

    /**
     * [限时限购显示]
     * GY
     */
    public function limitations_index() 
    {
               
        return view('limitations_index');
    }


    /**
     * [限时限购编辑]
     * GY
     */
    public function limitations_edit()
    {
        return view('limitations_edit');
    }


    /**
     * [限时限购添加商品]
     * GY
     */
    public function limitations_add()
    {
        return view('limitations_add');
    }








}