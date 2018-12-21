<?php
/**
 * Created by PhpStorm.
 * User: CHEN
 * Date: 2018/7/14
 * Time: 22:00
 */
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\captcha\Captcha;

class Login extends Controller{

    /**
     * [用户登录]
     * 陈绪
     */
    public function index(){
        return view("login");
    }


	/**
     * [验证码]
     * @author 陈绪
     */
    public function captchas(){
        $captcha = new Captcha([
            'imageW'=>100,
            'imageH'=>48,
            'fontSize'=>18,
            'useNoise'=>false,
            'length'=>3,
        ]);
        return $captcha->entry();
    }


    /**
     * [登录检测并取出对应的角色]
     * @author 陈绪
     * @param Request $request
     */
    public function login(Request $request){
        if(!captcha_check($request->only("yzm")["yzm"])){
            //验证失败
            $this->error("验证码有误",url("admin/Login/index"));
            exit();
        };
        if ($request->isPost()){
            $username = $request->only("account")["account"];
            $passwd = $request->only("passwd")["passwd"];

            $userInfo = db("admin")->where("account",$username)->where("status","<>",1)->select();

            if (!$userInfo) {
                $this->success("账户名不正确或管理员以被停用",url("admin/Login/index"));
            }
            if (password_verify($passwd , $userInfo[0]["passwd"])) {
                Session("user_id", $userInfo[0]["id"]);
                unset($userInfo->user_passwd);
                Session("user_info", $userInfo);
               // $this->redirect(url("admin/index/index"));
                $this->success("登录成功",url("admin/Index/index"));
            }else{
                $this->success("账户密码不正确",url("admin/Login/index"));

            }
        }
    }



    /**
     * [退出]
     * 陈绪
     */
    public function logout(){
        Session::delete("user_id");
        Session::delete("user_info");
        $this->redirect("admin/Login/index");
    }






}