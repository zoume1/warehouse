<?php
namespace app\admin\controller;

class Pages extends Controller
{
    public function getList(){
        $page=input('post.page');
        $limit=input('post.limit');

        $Pages = model('Pages');
        $Pages->getList($page,$limit);
        echo json_encode(['errno'=>'','errstr'=>'','data'=>$Pages->data]);
        exit;
    }

    public function addPage(){
        $content=input('post.content');
        $title=input('post.title');
        (int)$uid=input('post.uid');

        $Pages = model('Pages');
        if ($uid < 0) {
            $Pages->addPage($content,$title);
            echo json_encode(['errno'=>$Pages->errno,'errstr'=>$Pages->errstr,'data'=>$Pages->data]);
            exit;
 
        }else{

            $Pages->savePage($content,$title,$uid);
            echo json_encode(['errno'=>$Pages->errno,'errstr'=>$Pages->errstr]);
            exit;
        }
    }


    public function getOne(){
        $id=input('post.id');
        $Pages = model('Pages');
        $Pages->getOne($id);
        echo json_encode(['errno'=>'','errstr'=>'','data'=>$Pages->data]);
        exit;
    }

    public function delOne(){
        $id=input('id');
        Db::name('pages')->where(['id'=>$id])->delete();
    }
}