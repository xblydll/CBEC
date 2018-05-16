<?php  
namespace Admin\Controller;
class SystemController extends BaseController{
	public function config(){
		if(IS_POST){
			$base = I('base');
			$data = array();
			foreach($base as $k=>$v){
				$data[] = array(
					'name' => $k,
					'value' => $v,
					'type' => 'base'
				);
			}
			$db = M('config');
			foreach($data as $v){
				$row = $db->where(array('name'=>$v['name'],'type'=>$v['type']))->find();
				if(!empty($row)){
					$v['id'] = $row['id'];
					$db->save($v);
				}else{
					$db->add($v);
				}
			}
			$this->ajaxReturn(array('status'=>'y','info'=>'操作成功'));
		}else{
			$base = get_config('base');
			$this->assign('base',$base);
			$this->display();
		}
	}

	public function safety_question(){
		$list = M('safety_question')->select();
		$this->assign('list',$list);
		$this->display();
	}

	public function safety_question_add(){
		$db = M('safety_question');
		if(IS_POST){
			$data = I('post.');
			$data['conment'] = trim($data['conment']);
			if($data['id'] > 0){
				$res = $db->save($data);
			}else{
				$data['add_time'] = time();
				$res = $db->add($data);
			}
			if($res){
				$this->ajaxReturn(array('status'=>'y','info'=>'操作成功'));
			}else{
				$this->ajaxReturn(array('status'=>'n','info'=>'操作失败'));
			}
		}else {
			$id = I('id');
			$row = $db->find($id);
			$this->assign($row);
			$this->display();
		}
	}


	public function seedling_del(){
		$id = trim(I('id'),',');
		$b = M('safety_question')->delete($id);
		if($b){
			$this->ajaxreturn(array('status'=>'y','info'=>'删除成功'));
		}else{
			$this->ajaxreturn(array('status'=>'y','info'=>'删除成功'));
		}

	}
}