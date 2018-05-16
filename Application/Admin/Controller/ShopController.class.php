<?php
namespace Admin\Controller;
class ShopController extends BaseController {
	public function seedling_list(){
		$db = M('seedling');
		$where = array();
		$word = trim(I('word'));
		if(!empty($word) ){
			$where['eq_name|eq_id'] = array('like',"%$word%");
		}
		$where['type'] = 0;
		$count = $db->where($where)->count();
		$page = new \Think\Page($count,10);
		$list = $db->where($where)->limit($page->firstRow,$page->listRows)->select();
		$this->assign('count',$count);
		$this->assign('list',$list);
		$this->assign('page',$page->show());
		$this->display();
	}

	//荷苗添加
	public function seedling_add(){
		$db = M('seedling');
		if(IS_POST){
			$data = I('post.');
			$id = I('get.id');
			if($id > 0){
				$b = $db->save($data);
			}else{
				$b = $db->add($data);
			}
			if($b !== false){
				$this->ajaxreturn(array('status'=>'y','info'=>'操作成功'));
			}else{
				$this->ajaxreturn(array('status'=>'n','info'=>'操作失败'));
			}	
		}else{
			$id = I('get.id');
			$row = $db->find($id);
			$this->assign($row);
			$this->display();
		}
	}

	public function seedling_del(){

		$id = trim(I('id'),',');

		$b = M('seedling')->delete($id);

		if($b){

			$this->ajaxreturn(array('status'=>'y','info'=>'删除成功'));

		}else{

			$this->ajaxreturn(array('status'=>'y','info'=>'删除成功'));

		}

	}

	public function generate_list(){
		$db = M('generate');
		$where = array();
		
		$word = trim(I('word'));

		if(!empty($word) ){
			$where['state'] = $word;
		}

		$uid = I('get.uid');
		if(!empty($uid)){
			$where['uid'] = $uid;
		}

		$count = $db->where($where)->count();
		$page = new \Think\Page($count,10);
		$list = $db->where($where)->limit($page->firstRow,$page->listRows)->select();
		// $list['loutsall']=$list['zeng_lotus_old']+$list['zeng_lotus_new'];
		$this->assign('count',$count);
		$this->assign('list',$list);
		$this->assign('page',$page->show());
		$this->display();
	}

	public function generate_del(){
		$id = trim(I('id'),',');
		$b = M('generate')->delete($id);
		if($b){
			$this->ajaxreturn(array('status'=>'y','info'=>'删除成功'));
		}else{
			$this->ajaxreturn(array('status'=>'y','info'=>'删除成功'));
		}
	}

	##商品列表
	public function goods_list(){
		$db = M('seedling');
		$where = array();
		$word = trim(I('word'));
		if(!empty($word) ){
			$where['eq_name|eq_id'] = array('like',"%$word%");
		}
		$where['type'] = 1;
		$count = $db->where($where)->count();
		$page = new \Think\Page($count,10);
		$list = $db->where($where)->order('sort desc')->limit($page->firstRow,$page->listRows)->select();
		$this->assign('count',$count);
		$this->assign('list',$list);
		$this->assign('page',$page->show());
		$this->display();
	}

	//商品添加/修改
	public function goods_list_add(){
		$db = M('seedling');
		if(IS_POST){
			$data = I('post.');
			$id = I('get.id');
			if($id > 0){
				$b = $db->save($data);
			}else{
				$data['type'] = 1;
				$b = $db->add($data);
			}
			if($b !== false){
				$this->ajaxreturn(array('status'=>'y','info'=>'操作成功'));
			}else{
				$this->ajaxreturn(array('status'=>'n','info'=>'操作失败'));
			}	
		}else{
			$id = I('get.id');
			$row = $db->find($id);
			$this->assign($row);
			$this->display();
		}
	}
}