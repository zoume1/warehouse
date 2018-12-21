<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/21 0021
 * Time: 11:27
 */
namespace app\admin\controller;

use think\Controller;

class Test extends  Controller{

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:测试首页
     **************************************
     * @return \think\response\View
     */
    public function test_index(){
        return view("test_index");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:测试添加
     **************************************
     * @return \think\response\View
     */
    public function test_add(){
        return view("test_add");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:测试编辑
     **************************************
     * @return \think\response\View
     */
    public function test_edit(){
        return view("test_edit");
    }

}