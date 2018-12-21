<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/13 0013
 * Time: 16:55
 */

namespace app\admin\controller;


use think\Controller;

class  Order extends  Controller{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:初始订单页面
     **************************************
     * @return \think\response\View
     */
    public function order_index(){
        return view("order_index");
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:积分订单
     **************************************
     * @return \think\response\View
     */
    public function order_integral(){
        return view("order_integral");
    }




    /**
     **************李火生*******************
     * @param Request $request
     * Notes:交易设置
     **************************************
     * @return \think\response\View
     */
    public function transaction_setting(){
        return view("transaction_setting");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:退款维权
     **************************************
     * @return \think\response\View
     */
    public function refund_protection_index(){
        return view("refund_protection_index");
    }



}