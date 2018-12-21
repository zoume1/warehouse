<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/3 0003
 * Time: 18:21
 */

namespace  app\admin\controller;

use think\Controller;

class  Bonus extends  Controller{

    /**
     * [积分商城显示]
     * GY
     */
    public function bonus_index() 
    {
               
        return view('bonus_index');
    }


    /**
     * [积分商城编辑]
     * GY
     */
    public function bonus_edit()
    {
        return view('bonus_edit');
    }


    /**
     * [积分商城添加商品]
     * GY
     */
    public function bonus_add()
    {
        return view('bonus_add');
    }


    /**
     * [优惠券显示]
     * GY
     */
    public function coupon_index(){
        return view('coupon_index');
    }


    /**
     * [优惠券添加]
     * GY
     */
    public function coupon_add(){
        return view('coupon_add');
    }


    /**
     * [优惠券编辑]
     * GY
     */
    public function coupon_edit(){
        return view('coupon_edit');
    }







}