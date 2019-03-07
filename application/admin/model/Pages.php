<?php 
namespace app\admin\model;
use think\Model;
use think\Db;
class Pages extends Model
{
	public $errno = '';
	public $errstr = '';
	public $data = '';
	public function addPage($content,$title){
		$uid=Db::table('pages')->insertGetId(['content'=>$content,'title'=>$title,'create_time'=>time()]);
		if (!$uid) {
			$this->errno = '2001';
			$this->errstr = '数据添加失败';
			return false;
		}
		$this->data = $uid;
		return true;
	}

	public function savePage($content,$title,$uid){
		$res=Db::table('pages')->where(['uid'=>$uid])->update(['content'=>$content,'title'=>$title,'modify_time'=>time()]);

		if (!$res) {
			$this->errno = '2003';
			$this->errstr = '更新失败';
			return false;
		}
		return true;
	}

	public function getList($page=1,$limit=10){
        $count=Db::table('pages')->count();
        $start=($page-1)*$limit;
        $data=Db::table('pages')->limit($start,$limit)->select();
        $this->data = ['data'=>$data,'count'=>$count];
    }

    public function getOne($id){
        $data=Db::table('pages')->where(['id'=>$id])->find();
    }
}