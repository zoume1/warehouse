<?php
namespace app\admin\controller;
use think\Controller;
use  think\Db;
class Pages extends Controller
{
    public function getList(){
        $page=input('post.page');
        $limit=input('post.limit');
        $Pages = model("Pages")->getList($page,$limit);
        echo json_encode(['errno'=>'','errstr'=>'','data'=>$Pages->data]);
        exit;
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:小程序内容添加编辑
     **************************************
     */
    public function addPage(){
        $content=input('post.content');
        $title=input('post.title');
        (int)$uid=input('post.id');
        $Pages = model('Pages');
        if (!$uid) {
            $Pages->addPage($content,$title);
            echo json_encode(['errno'=>$Pages->errno,'errstr'=>$Pages->errstr,'data'=>$Pages->data]);
            exit;
        }else{
            $Pages->savePage($content,$title,$uid);
            echo json_encode(['errno'=>$Pages->errno,'errstr'=>$Pages->errstr,'data'=>$Pages->data]);
            exit;
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:获取里面得到数据
     **************************************
     */
    public function getOne(){
        $id=input('post.uid');
        $Pages = model('Pages');
        $Pages->getOne($id);
        echo json_encode(['errno'=>'','errstr'=>'','data'=>$Pages->data]);
        exit;
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:删除页面
     **************************************
     * @param $id
     */
    public function del($id){
      $bool =  Db::name('pages')->where(['id'=>$id])->delete();
      if($bool){
          $this->success("删除成功");
      }else{
          $this->error("删除失败");
      }
    }
}