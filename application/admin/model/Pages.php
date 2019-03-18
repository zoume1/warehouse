<?php 
namespace app\admin\model;
use think\Model;
use think\Db;
class Pages extends Model
{
	public $errno = '';
	public $errstr = '';
	public $data = '';
	//添加页面
	public function addPage($content,$title){
		$uid=Db::name('pages')->insertGetId(['content'=>$content,'title'=>$title,'create_time'=>time()]);
		if (!$uid) {
			$this->errno = '2001';
			$this->errstr = '数据添加失败';
			return false;
		}
		$this->data = $uid;
		return true;
	}
        //更新页面
	public function savePage($content,$title,$uid){
		$res=Db::name('pages')
            ->where('id',$uid)
            ->update(['content'=>$content,'title'=>$title,'modify_time'=>time(),"status"=>0]);
		if (!$res) {
			$this->errno = '2003';
			$this->errstr = '更新失败';
			return false;
		}
        $this->data = $uid;
		return true;
	}

	public function getList($page=1,$limit=10){
        $count=Db::name('pages')->count();
        $start=($page-1)*$limit;
        $data=Db::name('pages')->limit($start,$limit)->select();
        $this->data = ['data'=>$data,'count'=>$count];
    }

    //获取第一个数据
    public function getOne($id){
        $data=Db::name('pages')->where(['id'=>$id])->find();
        if (!$data) {
            $this->errno = '2001';
            $this->errstr = '数据添加失败';
            return false;
        }
        $this->data = $data;
        return true;
    }
}