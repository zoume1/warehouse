<?php
/**
 * Created by PhpStorm.
 * User: 李火生
 * Date: 2018/10/25
 * Time: 11:22
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;
use think\Request;

class User extends Controller{
    /**
     **************李火生*******************
     * @return \think\response\View
     * 会员首页
     **************************************
     */
    public function index(){
        $user_data =Db::name('member')->paginate(20);
        return view('index',['user_data'=>$user_data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:客户账户启用状态编辑
     **************************************
     */
    public function  status(Request $request){
        if($request->isPost()){
            $data =$_POST;
            if(!empty($data)){
                $bool =Db::name('member')->where('member_id',$data['id'])->update(['member_status'=>$data['status']]);
                if($bool){
                    return ajax_success('修改成功',['status'=>1]);
                }else{
                    return ajax_error('修改失败',['status'=>0]);
                }
            }
        }
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     * 会员编辑
     **************************************
     */
    public function edit($id){
        $member_data = Db::name('member')->where('member_id',$id)->find();
        $term_data =Db::name('member_grade')->select();
        return view('edit',['member_data'=>$member_data,'term_data'=>$term_data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:会员更新
     **************************************
     * @param $id
     */
    public function update($id){
        if($this->request->isPost()){
            $data = $data =$this->request->post();
            $grade_name =Db::name('member_grade')->field('member_grade_name')->where('member_grade_id',$data['member_grade_id'])->find();
            $data['member_grade_name']=$grade_name['member_grade_name'];
            if(!empty($id)){
                $bool =Db::name('member')->where('member_id',$id)->update($data);
                if($bool){
                    $this->success('编辑成功','admin/User/index');
                }else{
                    $this->error('编辑失败');
                }
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:会员删除
     **************************************
     * @param $id
     */
    public function del($id){
        $bool = db("member_grade")->where("member_id", $id)->delete();
        if ($bool) {
            $this->success("删除成功", url("admin/User/index"));
        } else {
            $this->error("删除失败", url("admin/User/index"));
        }
    }



    /**
     **************李火生*******************
     * @return \think\response\View
     * 会员等级
     **************************************
     */
    public function  grade(){
       $grade_data = Db::name('member_grade')->paginate(20);
        return view('grade',['grade_data'=>$grade_data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:会员等级编辑(添加编辑都在这)
     **************************************
     * @param null $id
     * @return \think\response\View
     */
    public function grade_edit($id =null){
        $term_data =Db::name('term')->select();
        if($id > 0){
            $info =Db::name('member_grade')->where("member_grade_id",$id)->find();
            $this->assign('info',$info);
        }
        if($this->request->isPost()){
            $data =$this->request->post();
            $data['create_time'] =time();
            $file =$this->request->file("member_grade_img");
            if($file){
                $datas = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                $images_url = str_replace("\\","/",$datas->getSaveName());
                $data['member_grade_img'] =$images_url;
            }
            if($id > 0){
                $res =Db::name('member_grade')->where('member_grade_id',$id)->update($data);
            }else{
                $res =Db::name('member_grade')->insertGetId($data);
            }
            if($res>0){
                $this->success('编辑成功','admin/User/grade');
            }else{
                $this->error('编辑失败');
            }
        }
        return view('grade_edit',['term_data'=>$term_data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:会员等级添加（写在编辑里面）
     **************************************
     * @return \think\response\View
     */
	public function  grade_add(){
		return view('grade_add');
	}

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:会员等级图片删除
     **************************************
     * @param Request $request
     */
	public function  grade_start_image_del(Request $request){
        if ($request->isPost()) {
            $id = $request->only(['id'])['id'];
            $image_url = Db::name('member_grade')->where("member_grade_id", $id)->field("member_grade_img")->find();
            if ($image_url['member_grade_img'] != null) {
                unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $image_url['member_grade_img']);
            }
            $bool = Db::name('member_grade')->where("member_grade_id", $id)->field("member_grade_img")->update(["member_grade_img" => null]);
            if ($bool) {
                return ajax_success("删除成功");
            } else {
                return ajax_error("删除失败");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:会员等级列表删除（注意没有把图片删除，其他地方有用到）
     **************************************
     * @param $id
     */
    public function grade_del($id){
        $bool = db("member_grade")->where("member_grade_id", $id)->delete();
        if ($bool) {
            $this->success("删除成功", url("admin/User/grade"));
        } else {
            $this->error("删除失败", url("admin/User/grade"));
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:会员等级列表状态值修改
     **************************************
     * @param Request $request
     */
    public function  grade_status(Request $request){
        if($request->isPost()){
            $data =$_POST;
            if(!empty($data)){
                $bool =Db::name('member_grade')->where('member_grade_id',$data['id'])->update(['introduction_display'=>$data['status']]);
                if($bool){
                    return ajax_success('修改成功',['status'=>1]);
                }else{
                    return ajax_error('修改失败',['status'=>0]);
                }
            }
        }
    }





}