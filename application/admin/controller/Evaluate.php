<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/13 0013
 * Time: 17:31
 */
namespace app\admin\controller;


use think\Controller;

class Evaluate extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:评价管理页面
     **************************************
     * @return \think\response\View
     */
    public function evaluate_index(){
    return view("evaluate_index");
}

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:评价编辑
     **************************************
     * @return \think\response\View
     */
    public function evaluate_edit(){
        return view("evaluate_edit");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:评价积分设置
     **************************************
     * @return \think\response\View
     */
    public function evaluate_setting(){
        return view("evaluate_setting");
    }

}