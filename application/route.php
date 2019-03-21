<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;


/**
 * [前端路由]
 * 陈绪
 */
Route::group("",[
    /*首页*/
    "/$"=>"index/index/index",

    /*TODO：start*/
    /*登录授权*/
    "wechatlogin"=>"index/Login/wechatlogin",  //登录授权
    "my_show_grade"=>"index/My/show_grade",  //会员等级
    "my_index"=>"index/My/my_index",  //我的页面
    "wxpay"=>"index/WechatPay/wxpay",//微信支付测试
    "Wx_Pay"=>"index/Test/Wx_Pay", //测试
    /*TODO:end*/

    /*茶圈*/
    "teacenter_data"=>"index/TeaCenter/teacenter_data",          //茶圈父级显示
    "teacenter_display"=>"index/TeaCenter/teacenter_display",    //茶圈分类显示
    "teacenter_activity"=>"index/TeaCenter/teacenter_activity",  //茶圈活动页面显示
    "teacenter_detailed"=>"index/TeaCenter/teacenter_detailed",  //茶圈活动详细显示
    "teacenter_alls"=>"index/TeaCenter/teacenter_alls",          //茶圈所有活动
    "teacenter_recommend"=>"index/TeaCenter/recommend",          //茶圈首页推荐活动
]);

/**
 * [后台路由]
 * 陈绪
 */
Route::group("admin",[
    /*首页*/
    "/$"=>"admin/index/index",

    /* 后台首页 */
    "home_index"=>"admin/Home/index",

    /*登录页面*/
    "index"=>"admin/Login/index",
    "login"=>"admin/Login/login",
    "logout"=>"admin/Login/logout",

    /*验证码*/
    "login_captcha"=>"admin/Login/captchas",

    /*管理员列表*/
    "admin_index"=>"admin/admin/index",
    "admin_add"=>"admin/admin/add",
    "admin_save"=>"admin/admin/save",
    "admin_del"=>"admin/admin/del",
    "admin_edit"=>"admin/admin/edit",
    "admin_updata"=>"admin/admin/updata",
    "admin_status"=>"admin/admin/status",
    "admin_passwd"=>"admin/admin/passwd",


    /*菜单列表*/
    "menu_index"=>"admin/menu/index",
    "menu_add"=>"admin/menu/add",
    "menu_save"=>"admin/menu/save",
    "menu_del"=>"admin/menu/del",
    "menu_edit"=>"admin/menu/edit",
    "menu_updata"=>"admin/menu/updata",
    "menu_status"=>"admin/menu/status",


    /*角色列表*/
    "role_index"=>"admin/role/index",
    "role_add"=>"admin/role/add",
    "role_save"=>"admin/role/save",
    "role_del"=>"admin/role/del",
    "role_edit"=>"admin/role/edit",
    "role_updata"=>"admin/role/updata",
    "role_status"=>"admin/role/status",

    /*店铺*/
    "general_index"=>"admin/General/general_index", //店铺信息
    "small_routine_index"=>"admin/General/small_routine_index", //小程序设置
    "decoration_routine_index"=>"admin/General/decoration_routine_index", //小程序装修
    "decoration_routine_details"=>"admin/General/decoration_routine_details", //小程序装修详情
    /*TODO：小程序装修2版本start*/
    "show_index"=>"admin/StyleDiy/index",//小程序首页
    //详情页在test_add
    "diypage_index"=>"admin/Diypage/index",//小程序页面保存
    /*TODO：小程序装修2版本end*/

    /*TODO：会员管理开始*/
    "user_index"=>"admin/User/index", //会员概况
    "user_status"=>"admin/User/status", //会员状态编辑
    "user_edit"=>"admin/User/edit",     //会员编辑
    "user_update"=>"admin/User/update",     //会员信息更新
    "user_del"=>"admin/User/del",     //会员删除

    "user_grade"=>"admin/User/grade",  //会员等级
	"user_grade_edit"=>"admin/User/grade_edit",  //会员等级编辑
     "user_grade_add"=>"admin/User/grade_add",  //会员等级添加（写在编辑里面了）
     "user_grade_del"=>"admin/User/grade_del",  //会员等级删除
     "user_grade_start_image_del"=>"admin/User/grade_start_image_del",  //会员等级图片删除
    "user_grade_status"=>"admin/User/grade_status",  //会员等级状态修改
    /*TODO:会员管理结束*/

    /*TODO：会员储值开始*/
    /*资金管理*/
    "capital_index"=>"admin/Capital/index",  //资金管理界面
	"capital_edit"=>"admin/Capital/edit", //资金管理界面edit
	"capital_add"=>"admin/Capital/add", //资金管理界面add
	"capital_del"=>"admin/Capital/del", //资金管理删除del
	"capital_status"=>"admin/Capital/status", //资金管理状态修改
    /*TODO:会员储值结束*/


    /* TODO:图片库开始*/
	"photo_index"=>"admin/Photo/index",
    "images_online_push"=>"admin/Photo/images_online_push", //上传图片库
    "photo_del"=>"admin/Photo/delete", //删除单张图片
    /* TODO:图片库结束*/
    /*TODO:小程序上传图片开始*/
    "img_upload"=>"admin/Upload/img_upload",//小程序上传图片
    /*TODO:小程序上传图片结束*/






    /*TODO:订单开始*/
    "order_index"=>"admin/Order/order_index",//初始订单页面
    "order_integral"=>"admin/Order/order_integral",//积分订单
    "transaction_setting"=>"admin/Order/transaction_setting",//交易设置
    "refund_protection_index"=>"admin/Order/refund_protection_index",//退款维权
    /*TODO:订单结束*/
    /*TODO:评价开始*/
    "evaluate_index"=>"admin/Evaluate/evaluate_index",//评价管理页面
    "evaluate_edit"=>"admin/Evaluate/evaluate_edit",//评价编辑
    "evaluate_setting"=>"admin/Evaluate/evaluate_setting",//评价积分设置
    /*TODO:评价结束*/

	 /*茶圈*/
    "category_index"=>"admin/Category/index",   //活动分类显示
    "category_add"=>"admin/Category/add",       //活动分类添加
    "category_save"=>"admin/Category/save",     //活动分类分组入库
    "category_edit"=>"admin/Category/edit",     //活动分类分组修改
    "category_del"=>"admin/Category/del",       //活动分类分组删除
    "category_updata"=>"admin/Category/updata", //活动分类分组更新
    "category_ajax"=>"admin/Category/ajax_add", //活动分类分组ajax显示
    "category_dels"=>"admin/Category/dels",     //活动分类批量删除
    "category_images"=>"admin/Category/images", //活动分类图片删除
    "category_status"=>"admin/Category/status", //活动分类分组状态修改
    "category_search"=>"admin/Category/search", //活动分类分组状态修改

    "accessories_business_advertising"=>"admin/Advertisement/index",                 //活动管理分组显示
    "accessories_business_add"=>"admin/Advertisement/accessories_business_add",      //活动管理分组添加
    "accessories_business_edit"=>"admin/Advertisement/accessories_business_edit",    //活动管理分组编辑
    "accessories_business_save"=>"admin/Advertisement/accessories_business_save",    //活动管理分组保存
    "accessories_business_updata"=>"admin/Advertisement/accessories_business_updata",//活动管理分组保存
    "accessories_business_del"=>"admin/Advertisement/accessories_business_del",      //活动管理分组删除
    "accessories_business_images"=>"admin/Advertisement/accessories_business_images",//活动管理分组图片删除
    "accessories_business_dels"=>"admin/Advertisement/accessories_business_dels",    //活动管理分组批量删除(前端没写)
    "accessories_business_label"=>"admin/Advertisement/accessories_business_label",  //活动管理分组标签修改
    "accessories_business_search"=>"admin/Advertisement/accessories_business_search",//活动管理分组模糊搜索

	 "comments_index"=>"admin/Comments/index",       //评论管理显示
	 "comments_add"=>"admin/Comments/add",           //评论积分设置
	 "comments_preserve"=>"admin/Comments/preserve", //评论积分设置保存
	 "comments_edit"=>"admin/Comments/edit",         //评论管理编辑
     "comments_save"=>"admin/Comments/save",         //评论管理保存
     "comments_status"=>"admin/Comments/status",     //评论管理状态修改
     "comments_delete"=>"admin/Comments/delete",     //评论管理组删除
     "comments_deletes"=>"admin/Comments/deletes",   //评论管理组批量删除
     "comments_search"=>"admin/Comments/search",     //评论管理组模糊搜索


     "active_order_index"=>"admin/ActiveOrder/index",   //活动订单显示
     "active_order_search"=>"admin/ActiveOrder/search", //评论管理组模糊搜索

    /*普通商品列表*/
    "goods_index"=>"admin/Goods/index",      //商品列表显示
    "goods_add"=>"admin/Goods/add",          //商品列表组添加
    "goods_save"=>"admin/Goods/save",        //商品列表组保存入库
    "goods_edit"=>"admin/Goods/edit",        //商品列表组编辑
    "goods_updata"=>"admin/Goods/updata",    //商品列表组更新
    "goods_status"=>"admin/Goods/status",    //商品列表组首页推荐
    "goods_del"=>"admin/Goods/del",          //商品列表组删除
    "goods_dels"=>"admin/Goods/dels",        //商品列表组批量删除
    "goods_search"=>"admin/Goods/search",    //商品列表组模糊搜索
    "goods_images"=>"admin/Goods/images",    //商品列表组图片删除
    "goods_photos"=>"admin/Goods/photos",    //商品列表规格图片删除
    "goods_value"=>"admin/Goods/value",      //商品列表规格值修改
    "goods_switches"=>"admin/Goods/switches",//商品列表规格开关
    "goods_addphoto"=>"admin/Goods/addphoto",//商品列表规格图片添加



    /*众筹商品*/
    "crowd_index"=>"admin/Crowd/crowd_index",  //众筹商品显示

    /*专属定制*/
    "custom_made"=>"admin/Made/custom_made",  //专属定制

    /*仓储*/
    "store_house" =>"admin/StoreHouse/store_house",      //仓库管理
    "stores_divergence" =>"admin/StoreHouse/stores_divergence",//出入仓

    /*资产*/
    "property_index" =>"admin/Property/property_index",

    /*物联*/
    "anti_fake" =>"admin/Property/anti_fake",                 //防伪溯源
    "direct_seeding" =>"admin/Property/direct_seeding",       //视频直播
    "interaction_index" =>"admin/Property/interaction_index", //温湿感应

    /*数据*/
    "data_index" =>"admin/Information/data_index",                //数据概况
    "analytical_index" =>"admin/Information/analytical_index",    //溯源分析

    /*设置*/
    "module_index" =>"admin/InterCalate/module_index",              //通用模块
    "business_index" =>"admin/InterCalate/business_index",          //运营模块
    "dispatching_index" =>"admin/InterCalate/dispatching_index",    //配送设置






    /*商品分类*/
    "goods_type_index"=>"admin/GoodsType/index",      //商品分类列表显示
    "goods_type_add"=>"admin/GoodsType/add",          //商品分类列表增加
    "goods_type_edit"=>"admin/GoodsType/edit",        //商品分类列表编辑
    "goods_type_save"=>"admin/GoodsType/save",        //商品分类列表组入库
    "goods_type_updata"=>"admin/GoodsType/updata",    //商品分类列表组更新
    "goods_type_del"=>"admin/GoodsType/del",          //商品分类列表组删除 
    "goods_type_ajax_add"=>"admin/GoodsType/ajax_add",//商品分类列表组ajax显示
    "goods_type_dels"=>"admin/GoodsType/dels",        //商品分类列表组批量删除
    "goods_type_search"=>"admin/GoodsType/search",    //商品分类列表组模糊搜索 

    /*TODO：分销开始*/
    "distribution_setting_index"=>"admin/Distribution/setting_index",  //分销设置页面
    "distribution_setting_edit"=>"admin/Distribution/setting_edit",    //分销设置页面编辑
    "distribution_setting_updata"=>"admin/Distribution/setting_updata",//分销设置页面保存
    "distribution_goods_index"=>"admin/Distribution/goods_index",      //分销商品页面
    "distribution_goods_add"=>"admin/Distribution/goods_add",          //分销商品添加
    "distribution_goods_edit"=>"admin/Distribution/goods_edit",        //分销商品编辑
    "distribution_goods_save"=>"admin/Distribution/goods_save",        //分销商品添加入库
    "distribution_goods_update"=>"admin/Distribution/goods_update",    //分销商品编辑更新
    "distribution_goods_delete"=>"admin/Distribution/goods_delete",    //分销商品组删除
    "distribution_record_index"=>"admin/Distribution/record_index",    //分销记录页面
    "distribution_member_index"=>"admin/Distribution/member_index",    //分销成员页面
    "distribution_member_edit"=>"admin/Distribution/member_edit",      //分销成员页面编辑
    /*TODO：分销结束*/




    /*积分商城*/
    "bonus_index"=>"admin/Bonus/bonus_index",            
    "bonus_edit"=>"admin/Bonus/bonus_edit",              
    "bonus_add"=>"admin/Bonus/bonus_add",               

    /*限时限购*/
    "limitations_index"=>"admin/Limitations/limitations_index",    
    "limitations_edit"=>"admin/Limitations/limitations_edit",      
    "limitations_add"=>"admin/Limitations/limitations_add",
       

    /*优惠券*/
    "coupon_index"=>"admin/Bonus/coupon_index",    
    "coupon_edit"=>"admin/Bonus/coupon_edit",      
    "coupon_add"=>"admin/Bonus/coupon_add",    

   /*TODO:小程序装修页面分页开始*/
   "pages_del"=>"admin/Pages/del",
   "add_page"=>"admin/Pages/addPage",
   "get_one"=>"admin/Pages/getOne",
   /*TODO:小程序装修页面分页结束*/

   /*TODO:测试使用开始*/
   "test_index"=>"admin/Test/test_index",//测试首页
   "test_add"=>"admin/Test/test_add",//测试添加页面
   "test_edit"=>"admin/Test/test_edit",//测试编辑页面
    "test_list"=>"admin/Test/test_list",//测试分类
    "test_cart"=>"admin/Test/test_cart",//测试购物
   /*TODO:测试使用结束*/



    


]);

Route::miss("public/miss");


