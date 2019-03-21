<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/21 0021
 * Time: 11:27
 */
namespace app\admin\controller;

use think\Controller;
use think\Db;

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
        $appletid = input("appletid");
        $res = Db::table('applet')->where("id",$appletid)->find();
        $a=Db::table('ims_sudu8_page_base')->where("uniacid",$appletid)->find();
        $bg_music=$a['diy_bg_music'];
        if(!$res){
            $this->error("找不到对应的小程序！");
        }
        $this->assign('applet',$res);
        $op=input("op");
        $tplid=input("tplid");
        if($op){
            if($op=="setindex"){

                $val = input('v');

                $key_id = input('key_id');

                if(empty($key_id)){

                    return false;
                }

                if($val == 1){

                    Db::table('ims_sudu8_page_diypage')->where("uniacid",$appletid)->update(array("index"=>0));
                    $result = Db::table('ims_sudu8_page_diypage')->where("uniacid",$appletid)->where("id",$key_id)->update(array("index"=>1));
                }else{
                    $result = Db::table('ims_sudu8_page_diypage')->where("uniacid",$appletid)->where("id",$key_id)->update(array("index"=>0));
                }

                if($result){

                    return  json_encode(['status' => 1,'result' => ['returndata' => 1]]);

                }else{

                    return json_encode(['status' => 0]);

                }
            }
            if($op == "query"){

                $type = input('type');

                $kw = input('kw');

                switch ($type){

                    case 'news':


                        $list = Db::table('ims_sudu8_page_products')->where("uniacid",$appletid)->where("type","showArt")->where("title","like","%".$kw."%")->field("id,title")->select();


                        $html = '';


                        if($list){
                            foreach ($list as $k => $v){

                                $html .= '<div class="line">

                                                    <div class="icon icon-link1"></div>

                                                    <nav data-href="/sudu8_page/showArt/showArt?id='.$v['id'].'" data-linktype="page" class="btn btn-default btn-sm" title="选择">选择</nav>

                                                    <div class="text"><span class="label lable-default">普通</span>'.$v['title'].'</div>

                                                </div>';

                            }
                        }else{
                            $html = '<div class="line">

                                            无相关搜索结果

                                        </div>';
                        }

                        break;
                    case 'pic':

                        $list = Db::table('ims_sudu8_page_products')->where("uniacid",$appletid)->where("type","showPic")->where("title","like","%".$kw."%")->field("id,title")->select();



                        $html = '';


                        if($list){

                            foreach ($list as $k => $v){

                                $html .= '<div class="line">

                                                    <div class="icon icon-link1"></div>

                                                    <nav data-href="/sudu8_page/showPic/showPic?id='.$v['id'].'" data-linktype="page" class="btn btn-default btn-sm" title="选择">选择</nav>

                                                    <div class="text"><span class="label lable-default">普通</span>'.$v['title'].'</div>

                                                </div>';

                            }
                        }else{
                            $html = '<div class="line">

                                            无相关搜索结果

                                        </div>';
                        }

                        break;

                    case 'goods':


                        $list = Db::table('ims_sudu8_page_products')->where("uniacid",$appletid)->where("type","neq","showArt")->where("type","neq","showPic")->where("type","neq","wxapp")->where("title","like","%".$kw."%")->field("id,title,price,pro_kc,pro_flag")->select();

                        $html = '';


                        if($list){
                            foreach ($list as $k => $v){

                                if($v['pro_flag'] == 2){

                                    $url = "/sudu8_page/showProMore/showProMore?id=".$v['id'];

                                    $g = "多规格";

                                }else{

                                    $url = "/sudu8_page/showPro/showPro?id=".$v['id'];

                                    $g = "单规格";

                                }

                                $html .= '<div class="line">

                                                    <div class="icon icon-link1"></div>

                                                    <nav data-href="'.$url.'" data-linktype="page" class="btn btn-default btn-sm" title="选择">选择</nav>

                                                    <div class="text"><span class="label lable-default">普通</span>'.$g.' - 商品名称：'.$v['title'].' &nbsp; 价格：'.$v['price'].' &nbsp; 库存：'.$v['pro_kc'].'</div>

                                                </div>';

                            }
                        }else{
                            $html = '<div class="line">

                                            无相关搜索结果

                                        </div>';
                        }

                        break;
                }

                echo $html;
                exit;
            }
            if ($op == 'delpage'){
                $tpl_id = input("tplid");
                $tpl_pages = Db::table('ims_sudu8_page_diypagetpl')->where("uniacid",$appletid)->where("id",$tpl_id)->find()['pageid'];

                $tpl_pages_arr = explode(",",$tpl_pages);
                $tpl_pages_count = Db::table('ims_sudu8_page_diypage')->where("uniacid",$appletid)->where("id","in",$tpl_pages_arr)->count();
                if($tpl_pages_count == 1){
                    $this->error('删除失败，模板必须保留一个页面');

                    exit;
                }



                $id = input('id') ? intval(input('id')) : 0;

                if($id == 0){

                    $this->error('参数错误');

                    exit;

                }

                $is_index = Db::table('ims_sudu8_page_diypage')->where("uniacid",$appletid)->where("id",$id)->where("index",1)->find();
                if($is_index){
                    $this->error("当前页面为首页不可删除");
                    exit;
                }
                $result = Db::table('ims_sudu8_page_diypage')->where("uniacid",$appletid)->where("id",$id)->delete();

                if($result){
                    $this->success("删除成功");

                }else{
                    $this->error('删除失败');

                }

            }
            if($op == "setsave"){
                // $pid = input('key_id');
                $is = Db::table('ims_sudu8_page_diypageset')->where("uniacid",$appletid)->find();
                // $is = Db::table('ims_sudu8_page_diypageset')->where("uniacid",$appletid)->where("pid",$pid)->find();
                $go_home = input('go_home');
                $kp = input('kp');
                $kp_is = input('kp_is');
                $kp_m = input('kp_m');
                $kp_url = input('kp_url');
                $kp_urltype = input('kp_urltype');
                $tc_is = input('tc_is');
                $tc = input('tc');
                $tc_url = input('tc_url');
                $tc_urltype = input('tc_urltype');
                $foot_is = input('foot_is');
                $bg_music = input('bg_music');
                $data = array(
                    // "pid"=>$pid,
                    "go_home"=>$go_home,
                    "kp"=>remote($appletid,$kp,2),
                    "kp_is"=>intval($kp_is),
                    "kp_m"=>intval($kp_m),
                    "kp_url"=>$kp_url,
                    "kp_urltype"=>$kp_urltype,
                    "tc_is"=>$tc_is,
                    "tc"=>remote($appletid,$tc,2),
                    "tc_url"=>$tc_url,
                    "tc_urltype"=>$tc_urltype,
                    "foot_is"=>$foot_is,
                );
                Db::table("ims_sudu8_page_base")->where("uniacid",$appletid)->update(array("diy_bg_music"=>$bg_music));
                if($is){
                    $res = Db::table('ims_sudu8_page_diypageset')->where("uniacid",$appletid)->update($data);
                }else{
                    $data['uniacid'] = $appletid;
                    $res = Db::table('ims_sudu8_page_diypageset')->insert($data);
                }
                if($res==1){
                    return 1;
                }else{
                    return 2;
                }
            }
            if ($op == 'add'){

                $data = $_POST;

                if(isset($data['data']['page']['url']) && $data['data']['page']['url'] != ""){
                    $data['data']['page']['url'] = remote($appletid,$data['data']['page']['url'],2);
                }

                if(isset($data['data']['page']['name']) && $data['data']['page']['name'] != ''){

                    $sd = [];

                    $sd['tpl_name'] = $data['data']['page']['name'];
                    if(isset($data['data']['page']['url']) && $data['data']['page']['url'] != ""){
                        $data['data']['page']['url'] = remote($appletid,$data['data']['page']['url'],2);
                    }

                    $sd['page'] = serialize($data['data']['page']);
                    if(strpos($sd['page'], "\\") !== false){
                        echo json_encode(['status' => -1,'message' => '保存失败，请去除特殊字符“\”再保存'],JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    if(isset($data['data']['items'])){
                        foreach($data['data']['items'] as $ki => $vi){
                            if($vi['id'] == "video" ){
                                if(!empty($vi['params']['videourl'])){
                                    if(strpos($vi['params']['videourl'],"</iframe>") !== false || strpos($vi['params']['videourl'],"</embed>") !== false){
                                        $data['data']['items'][$ki]['params']['videourl'] = "";
                                    }
                                }
                            }
                            if($vi['id'] == "yuyin" ){
                                if(!empty($vi['params']['linkurl'])){
                                    if(strpos($vi['params']['linkurl'],"</iframe>") !== false || strpos($vi['params']['linkurl'],"</embed>") !== false){
                                        $data['data']['items'][$ki]['params']['linkurl'] = "";
                                    }
                                }
                                if(!isset($vi['params']['backgroundimg'])){
                                    $data['data']['items'][$ki]['params']['backgroundimg'] = '';
                                }
                            }
                        }
                    }
                    if(isset($data['data']['items']) && $data['data']['items'] != ""){
                        foreach ($data['data']['items'] as $k => &$v) {
                            if($v['id'] == 'title2' || $v['id'] == 'title' || $v['id'] == 'line' || $v['id'] == 'blank' || $v['id'] == 'anniu' || $v['id'] == 'notice' || $v['id'] == 'service' || $v['id'] == 'listmenu' || $v['id'] == 'joblist' || $v['id'] == 'personlist' || $v['id'] == 'msmk' || $v['id'] == 'multiple' || $v['id'] == 'mlist' || $v['id'] == 'goods' || $v['id'] == 'tabbar' || $v['id'] == 'cases' || $v['id'] == 'listdesc' || $v['id'] == 'pt' || $v['id'] == 'dt' || $v['id'] == 'ssk' || $v['id'] == 'xnlf' || $v['id'] == 'yhq' || $v['id'] == 'dnfw' || $v['id'] == 'yuyin' || $v['id'] == 'feedback' || $v['id'] == 'yuyin'){
                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                }
                            }
                            if($v['id'] == 'bigimg' || $v['id'] == 'classfit' || $v['id'] == 'banner' || $v['id'] == 'menu' || $v['id'] == 'picture' || $v['id'] == 'picturew'){

                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                }

                                if($v['data']){
                                    foreach ($v['data'] as $ki => $vi) {
                                        if($vi['imgurl'] != ""){
                                            $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],2);
                                        }
                                    }
                                }
                            }
                            if($v['id'] == 'contact'){

                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);

                                }
                                if($v['params']['src'] != ""){
                                    $v['params']['src'] = remote($appletid,$v['params']['src'],2);
                                }
                                if($v['params']['ewm'] != ""){
                                    $v['params']['ewm'] = remote($appletid,$v['params']['ewm'],2);
                                }
                            }
                            if($v['id'] == 'video'){

                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                }

                                if($v['params']['poster'] != ""){
                                    $v['params']['poster'] = remote($appletid,$v['params']['poster'],2);
                                }
                            }
                            if($v['id'] == 'logo' || $v['id'] == 'dp'){

                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);

                                }
                                if($v['params']['src'] != ""){
                                    $v['params']['src'] = remote($appletid,$v['params']['src'],2);
                                }
                            }
                            if($v['id'] == 'footmenu'){
                                if($v['data']){
                                    foreach ($v['data'] as $ki => $vi) {
                                        if($vi['imgurl'] != ""){
                                            $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],2);
                                        }
                                    }
                                }
                            }
                        }
                        $sd['items'] = serialize($data['data']['items']);
                        if(strpos($sd['items'], "\\") !== false){
                            echo json_encode(['status' => -1,'message' => '保存失败，请去除特殊字符“\”再保存'],JSON_UNESCAPED_UNICODE);
                            exit;
                        }
                    }else{
                        $sd['items'] = "";
                    }


                    $sd['uniacid'] = $appletid;



                    if(intval($data['id']) == 0){

                        // $tplid = input('tplid');


                        /*新创建*/

                        $idata = Db::table('ims_sudu8_page_diypage')->where("uniacid",$appletid)->where("tpl_name",$sd['tpl_name'])->find();

                        if($idata){

                            echo json_encode(['status' => 0,'message' => '创建页面名称重复','id' => 0],JSON_UNESCAPED_UNICODE);exit;

                        }
                        $is = Db::table('ims_sudu8_page_diypage')->where('uniacid',$appletid)->find();
                        if(!$is){
                            $sd['index'] = 1;
                        }
                        $result = Db::table('ims_sudu8_page_diypage')->insert($sd);

                        $key = Db::table('ims_sudu8_page_diypage')->getLastInsID();

                        if($tplid>0){
                            $pageid =  Db::table('ims_sudu8_page_diypagetpl')->where("uniacid",$appletid)->where("id",$tplid)->field("pageid")->find()['pageid'];
                            Db::table('ims_sudu8_page_diypagetpl')->where("uniacid",$appletid)->where("id",$tplid)->update(array("pageid"=>$pageid.",".$key));
                        }


                    }else{

                        $result = Db::table('ims_sudu8_page_diypage')->where("uniacid",$appletid)->where("id",$data['id'])->update($sd);

                        $key = $data['id'];

                    }
                    if($result){

                        echo json_encode(['status' => 0,'message' => '保存成功','id' => $key],JSON_UNESCAPED_UNICODE);
                        exit;
                    }else{

                        echo json_encode(['status' => -1,'message' => '保存成功，本次保存未做修改'],JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                }
            }
            //另存为模板
            if ($op == 'settemplate') {
                $pageid = input('ids/a');
                $pageids = "";
                foreach ($pageid as $key => $value) {
                    $info = Db::table("ims_sudu8_page_diypage")->where("id",$value)->find();
                    $info['page'] = unserialize($info['page']);
                    if(isset($info['page']['url']) && $info['page']['url'] != ""){
                        $info['page']['url'] = remote($appletid,$info['page']['url'],2);
                    }

                    $items = unserialize($info['items']);
                    if($items){
                        foreach ($items as $k => $v) {
                            if($v['id'] == 'title2' || $v['id'] == 'title' || $v['id'] == 'line' || $v['id'] == 'blank' || $v['id'] == 'anniu' || $v['id'] == 'notice' || $v['id'] == 'service' || $v['id'] == 'listmenu' || $v['id'] == 'joblist' || $v['id'] == 'personlist' || $v['id'] == 'msmk' || $v['id'] == 'multiple' || $v['id'] == 'mlist' || $v['id'] == 'goods' || $v['id'] == 'tabbar' || $v['id'] == 'cases' || $v['id'] == 'listdesc' || $v['id'] == 'pt' || $v['id'] == 'dt' || $v['id'] == 'ssk' || $v['id'] == 'xnlf' || $v['id'] == 'yhq' || $v['id'] == 'dnfw' || $v['id'] == 'yuyin' || $v['id'] == 'feedback' || $v['id'] == 'yuyin'){
                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                }
                            }
                            if($v['id'] == 'bigimg' || $v['id'] == 'classfit' || $v['id'] == 'banner' || $v['id'] == 'menu' || $v['id'] == 'picture' || $v['id'] == 'picturew'){
                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                }
                                if($v['data']){
                                    foreach ($v['data'] as $ki => $vi) {
                                        if($vi['imgurl'] != ""){
                                            $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],2);
                                        }
                                    }
                                }
                            }
                            if($v['id'] == 'contact'){
                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                }
                                if($v['params']['src'] != ""){
                                    $v['params']['src'] = remote($appletid,$v['params']['src'],2);
                                }
                                if($v['params']['ewm'] != ""){
                                    $v['params']['ewm'] = remote($appletid,$v['params']['ewm'],2);
                                }
                            }
                            if($v['id'] == 'video'){
                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                }
                                if($v['params']['poster'] != ""){
                                    $v['params']['poster'] = remote($appletid,$v['params']['poster'],2);
                                }
                            }
                            if($v['id'] == 'logo' || $v['id'] == 'dp'){
                                if($v['params']['backgroundimg'] != ""){
                                    $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],2);
                                }
                                if($v['params']['src'] != ""){
                                    $v['params']['src'] = remote($appletid,$v['params']['src'],2);
                                }
                            }
                            if($v['id'] == 'footmenu'){
                                if($v['data']){
                                    foreach ($v['data'] as $ki => $vi) {
                                        if($vi['imgurl'] != ""){
                                            $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],2);
                                        }
                                    }
                                }
                            }

                            //去除栏目信息
                            //notice(公告) msmk(秒杀模块) goods(产品组) feedback(表单) pt(拼团) listdesc(文章) cases(图文)

                            if ($v['id'] == 'notice' || $v['id'] == 'msmk' || $v['id'] == 'goods' || $v['id'] == 'feedback' || $v['id'] == 'pt' || $v['id'] == 'listdesc' || $v['id'] == 'cases') {
                                $items[$k]['params']['sourceid'] = '';
                            }
                        }
                    }
                    $insert_id = Db::table('ims_sudu8_page_diypage_sys')->insertGetId(array(
                        'index' => $info['index'],
                        'page' => serialize($info['page']),
                        'items' => serialize($items),
                        'tpl_name' => $info['tpl_name'],
                    ));
                    $pageids = $pageids .','. $insert_id;
                }
                $pageids = substr($pageids,1);
                $data = [
                    'pageid' => $pageids,
                    'template_name' => input('name'),
                    'thumb' => input('preview'),
                    'create_time' => time()
                ];


                $key_id = Db::table("ims_sudu8_page_diypagetpl_sys")->insertGetId($data);

                echo json_encode(['status' => 1,'id' => $key_id,'message' => '保存成功'],JSON_UNESCAPED_UNICODE);
                exit;

            }
            if ($op == 'settemp') {
                $template_id = input('templateid');

                if($template_id > 0){

                    $data = [

                        // 'pageid' => implode(',',input('ids/a')),

                        'template_name' => input('name'),

                        'thumb' => remote($appletid,input('preview'),2),

                        'uniacid' => $appletid,

                        // 'create_time' => time()

                    ];

                    $res = Db::table("ims_sudu8_page_diypagetpl")->where("id",$template_id)->update($data);

                    if($res){
                        echo json_encode(['status' => 1],JSON_UNESCAPED_UNICODE);
                        exit;
                    }else{
                        echo json_encode(['status' => 0],JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                }
            }
        }else{
            //页面设置
            $setsave = Db::table("ims_sudu8_page_diypageset")->where("uniacid",$appletid)->find();
            if(!$setsave){
                $foot_is = 1;
                $setsave = [];
            }else{
                if($setsave['kp']){
                    $setsave['kp'] = remote($appletid,$setsave['kp'],1);
                }
                if($setsave['tc']){
                    $setsave['tc'] = remote($appletid,$setsave['tc'],1);
                }
                $foot_is = 0;
            }
            //查出当前模板关联页面id
            $type = input('type');
            if($type){
                $temp = Db::table("ims_sudu8_page_diypagetpl_sys")->where("id",$tplid)->find();
                if($temp['thumb']){
                    $temp['thumb'] = remote($appletid,$temp['thumb'],1);
                }
                if($temp['pageid'] == ""){
                    $pageid = Db::table("ims_sudu8_page_diypage_sys")->insertGetId(array(
                        'uniacid' => $appletid,
                        'index' => 1,
                        'page' => 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:18:"后台页面名称";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                        'items' => '',
                        'tpl_name' => '后台页面名称',
                    ));
                    Db::table("ims_sudu8_page_diypagetpl_sys")->where("id",$tplid)->update(array("pageid"=>$pageid));
                    $temp = Db::table("ims_sudu8_page_diypagetpl_sys")->where("id",$tplid)->find();
                }

                $pageidArray = explode(',',$temp['pageid']);


                //查出当前模板所有的页面
                $list = Db::table("ims_sudu8_page_diypage_sys")->where("id","in",$pageidArray)->field("id,tpl_name,index")->select();

                //页面操作
                $diypage = Db::table("ims_sudu8_page_diypage_sys")->where("id","in",$pageidArray)->where("index",1)->find();
                if($diypage == null){
                    $diypageone = Db::table("ims_sudu8_page_diypage_sys")->where("id","in",$pageidArray)->find();
                    Db::table("ims_sudu8_page_diypage_sys")->where("id",$diypageone['id'])->where("index",0)->update(array("index" => 1));
                    $diypage['id'] = $diypageone['id'];
                }
                $key_id = input('key_id') ? input('key_id') : $diypage['id'];  //显示页面id
                if($key_id>0){
                    $data = Db::table("ims_sudu8_page_diypage_sys")->where("id",$key_id)->find();
                    $data['page'] = unserialize($data['page']);
                    if(isset($data['page']['url']) && $data['page']['url'] != ""){
                        $data['page']['url'] = remote($appletid,$data['page']['url'],1);
                    }
                    $data['items'] = unserialize($data['items']);
                    if($data['items'] != ""){
                        if(isset($data['items']) && $data['items'] != ""){
                            foreach ($data['items'] as $k => &$v) {
                                if($v['id'] == 'title2' || $v['id'] == 'title' || $v['id'] == 'line' || $v['id'] == 'blank' || $v['id'] == 'anniu' || $v['id'] == 'notice' || $v['id'] == 'service' || $v['id'] == 'listmenu' || $v['id'] == 'joblist' || $v['id'] == 'personlist' || $v['id'] == 'msmk' || $v['id'] == 'multiple' || $v['id'] == 'mlist' || $v['id'] == 'goods' || $v['id'] == 'tabbar' || $v['id'] == 'cases' || $v['id'] == 'listdesc' || $v['id'] == 'pt' || $v['id'] == 'dt' || $v['id'] == 'ssk' || $v['id'] == 'xnlf' || $v['id'] == 'yhq' || $v['id'] == 'dnfw' || $v['id'] == 'yuyin' || $v['id'] == 'feedback' || $v['id'] == 'yuyin'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                }
                                if($v['id'] == 'bigimg' || $v['id'] == 'classfit' || $v['id'] == 'banner' || $v['id'] == 'menu' || $v['id'] == 'picture' || $v['id'] == 'picturew'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                    if($v['data']){
                                        foreach ($v['data'] as $ki => $vi) {
                                            if($vi['imgurl'] != "" && strpos($vi['imgurl'],"diypage/resource") === false){
                                                $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],1);
                                            }
                                        }
                                    }
                                }
                                if($v['id'] == 'contact'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                    if($v['params']['src'] != ""  && strpos($v['params']['src'],"diypage/resource") === false){
                                        $v['params']['src'] = remote($appletid,$v['params']['src'],1);
                                    }
                                    if($v['params']['ewm'] != ""  && strpos($v['params']['ewm'],"diypage/resource") === false){
                                        $v['params']['ewm'] = remote($appletid,$v['params']['ewm'],1);
                                    }
                                }
                                if($v['id'] == 'video'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                    if($v['params']['poster'] != "" && strpos($v['params']['poster'],"diypage/resource") === false){
                                        $v['params']['poster'] = remote($appletid,$v['params']['poster'],1);
                                    }
                                }
                                if($v['id'] == 'logo' || $v['id'] == 'dp'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                    if($v['params']['src'] != ""  && strpos($v['params']['src'],"diypage/resource") === false){
                                        $v['params']['src'] = remote($appletid,$v['params']['src'],1);
                                    }
                                }
                                if($v['id'] == 'footmenu'){
                                    if($v['data']){
                                        foreach ($v['data'] as $ki => $vi) {
                                            if($vi['imgurl'] != "" && strpos($vi['imgurl'],"diypage/resource") === false){
                                                $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],1);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $page = $data['page'];
                    if(isset($page['url']) && $page['url'] != ""){
                        $page['url'] = remote($appletid,$page['url'],1);
                    }
                    $diyform = Db::table("ims_sudu8_page_formlist")->where("uniacid",$appletid)->field("id,formname as title")->select();
                    $data['diyform'] = $diyform;
                    $data = json_encode($data, JSON_UNESCAPED_UNICODE);
                    $data = preg_replace("/\'/", "\'", $data);
                    $data = preg_replace('/(\\\n)/', "<br>", $data);

                }
            }else{
                $temp = Db::table("ims_sudu8_page_diypagetpl")->where("id",$tplid)->find();
                if($temp['thumb']){
                    $temp['thumb'] = remote($appletid,$temp['thumb'],1);
                }
                if($temp['pageid'] == ""){
                    $pageid = Db::table("ims_sudu8_page_diypage")->insertGetId(array(
                        'uniacid' => $appletid,
                        'index' => 1,
                        'page' => 'a:7:{s:10:"background";s:7:"#f1f1f1";s:13:"topbackground";s:7:"#ffffff";s:8:"topcolor";s:1:"1";s:9:"styledata";s:1:"0";s:5:"title";s:21:"小程序页面标题";s:4:"name";s:18:"后台页面名称";s:10:"visitlevel";a:2:{s:6:"member";s:0:"";s:10:"commission";s:0:"";}}',
                        'items' => '',
                        'tpl_name' => '后台页面名称',
                    ));
                    Db::table("ims_sudu8_page_diypagetpl")->where("id",$tplid)->update(array("pageid"=>$pageid));
                    $temp = Db::table("ims_sudu8_page_diypagetpl")->where("id",$tplid)->find();
                }
                //改变原来的模板状态为不启用
                $tpls = Db::table("ims_sudu8_page_diypagetpl")->where('uniacid',$appletid)->select();
                if($tpls){
                    foreach ($tpls as $k => $v) {
                        Db::table("ims_sudu8_page_diypagetpl")->where('uniacid',$appletid)->update(array('status' => 2));
                    }
                }
                Db::table("ims_sudu8_page_diypagetpl")->where("id",$tplid)->update(array("status"=>1));
                $pageidArray = explode(',',$temp['pageid']);
                //查出当前模板所有的页面
                $list = Db::table("ims_sudu8_page_diypage")->where("uniacid",$appletid)->where("id","in",$pageidArray)->field("id,tpl_name,index")->select();
                //页面操作
                $diypage = Db::table("ims_sudu8_page_diypage")->where("uniacid",$appletid)->where("id","in",$pageidArray)->where("index",1)->find();
                if($diypage == null){
                    $diypageone = Db::table("ims_sudu8_page_diypage")->where("uniacid",$appletid)->where("id","in",$pageidArray)->find();
                    Db::table("ims_sudu8_page_diypage")->where("uniacid",$appletid)->where("id",$diypageone['id'])->where("index",0)->update(array("index" => 1));
                    $diypage['id'] = $diypageone['id'];
                }
                $key_id = input('key_id') ? input('key_id') : $diypage['id'];  //显示页面id
                if($key_id>0){
                    $data = Db::table("ims_sudu8_page_diypage")->where("id",$key_id)->where("uniacid",$appletid)->find();
                    $data['page'] = unserialize($data['page']);
                    if(isset($data['page']['url']) && $data['page']['url'] != ""){
                        $data['page']['url'] = remote($appletid,$data['page']['url'],1);
                    }
                    $data['items'] = unserialize($data['items']);
                    if($data['items'] != ""){
                        if(isset($data['items']) && $data['items'] != ""){
                            foreach ($data['items'] as $k => &$v) {
                                if($v['id'] == 'title2' || $v['id'] == 'title' || $v['id'] == 'line' || $v['id'] == 'blank' || $v['id'] == 'anniu' || $v['id'] == 'notice' || $v['id'] == 'service' || $v['id'] == 'listmenu' || $v['id'] == 'joblist' || $v['id'] == 'personlist' || $v['id'] == 'msmk' || $v['id'] == 'multiple' || $v['id'] == 'mlist' || $v['id'] == 'goods' || $v['id'] == 'tabbar' || $v['id'] == 'cases' || $v['id'] == 'listdesc' || $v['id'] == 'pt' || $v['id'] == 'dt' || $v['id'] == 'ssk' || $v['id'] == 'xnlf' || $v['id'] == 'yhq' || $v['id'] == 'dnfw' || $v['id'] == 'feedback'  || $v['id'] == 'yuyin'){

                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                }
                                if($v['id'] == 'bigimg' || $v['id'] == 'classfit' || $v['id'] == 'banner' || $v['id'] == 'menu' || $v['id'] == 'picture' || $v['id'] == 'picturew'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                    if($v['data']){
                                        foreach ($v['data'] as $ki => $vi) {
                                            if($vi['imgurl'] != "" && strpos($vi['imgurl'],"diypage/resource") === false){
                                                $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],1);
                                            }
                                        }
                                    }
                                }
                                if($v['id'] == 'contact'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                    if($v['params']['src'] != ""  && strpos($v['params']['src'],"diypage/resource") === false){
                                        $v['params']['src'] = remote($appletid,$v['params']['src'],1);
                                    }
                                    if($v['params']['ewm'] != ""  && strpos($v['params']['ewm'],"diypage/resource") === false){
                                        $v['params']['ewm'] = remote($appletid,$v['params']['ewm'],1);
                                    }
                                }
                                if($v['id'] == 'video'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                    if($v['params']['poster'] != "" && strpos($v['params']['poster'],"diypage/resource") === false){
                                        $v['params']['poster'] = remote($appletid,$v['params']['poster'],1);
                                    }
                                }
                                if($v['id'] == 'logo' || $v['id'] == 'dp'){
                                    if($v['params']['backgroundimg'] != ""){
                                        $v['params']['backgroundimg'] = remote($appletid,$v['params']['backgroundimg'],1);
                                    }
                                    if($v['params']['src'] != ""  && strpos($v['params']['src'],"diypage/resource") === false){
                                        $v['params']['src'] = remote($appletid,$v['params']['src'],1);
                                    }
                                }
                                if($v['id'] == 'footmenu'){
                                    if($v['data']){
                                        foreach ($v['data'] as $ki => $vi) {
                                            if($vi['imgurl'] != "" && strpos($vi['imgurl'],"diypage/resource") === false){
                                                $v['data'][$ki]['imgurl'] = remote($appletid,$vi['imgurl'],1);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $page = $data['page'];
                    if(isset($page['url']) && $page['url'] != ""){
                        $page['url'] = remote($appletid,$page['url'],1);
                    }
                    $diyform = Db::table("ims_sudu8_page_formlist")->where("uniacid",$appletid)->field("id,formname as title")->select();
                    $data['diyform'] = $diyform;
                    $data = json_encode($data, JSON_UNESCAPED_UNICODE);
                    $data = preg_replace("/\'/", "\'", $data);
                    $data = preg_replace('/(\\\n)/', "<br>", $data);

                }
            }
            //到这一块进行模板赋值
            $this->assign("page",$page);
            $this->assign("template_id",$tplid);
            $this->assign("key_id",$key_id);
            $this->assign("list",$list);
            $this->assign("data",$data);
            $this->assign("setsave",$setsave);
            $this->assign("foot_is",$foot_is);
            $this->assign("temp",$temp);
            $this->assign("bg_music",$bg_music);
        }
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

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:测试分类
     **************************************
     * @return \think\response\View
     */
 public function test_list(){
        return view("test_list");
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:测试购物
     **************************************
     * @return \think\response\View
     */
 public function test_cart(){
        return view("test_cart");
    }

}