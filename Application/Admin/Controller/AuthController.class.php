<?php
namespace Admin\Controller;
class AuthController extends BaseController{
	/* 菜单列表/权限 */
	public function rule(){
		$db = M('auth_rule');
		$sort = I('sort');
		if(!empty($sort)){
			foreach($sort as $k=>$v){
				$v = intval($v);
				$db-> where(array('id'=>$k))->setField('sort',$v);
			}
		}

		$list = $db->order('sort')->select();
		$list = category_merge($list,0,0,'id');
		#print_r($list);
		$this->assign('list',$list);
		$this->display();
	}
	public function rule_add(){
		$db = M('auth_rule');
		$list = $db->order('sort')->select();

		if(IS_POST){
			#提交过程
			$data = I('post.');
			#print_r($data);die;
			#数据处理
			if(empty($data['name'])){
				$this->error('规则不能为空');die;
			}
			#操作方式
			if($_POST['id'] > 0){
				#不能把自己分到自己下级
				$children = get_children($list,$_POST['id'],'id');
				$children[] = $data['id'];
				#print_r($children);die;
				if(in_array($data['parent_id'], $children)){
					$this->error('不能使用自己或下级作为为上级分类');die;
				}
				$b = $db->save($data);
			}else{
				$b = $db->add($data);
			}
			#操作结果
			if($b !== flase){
				$this->ajaxReturn(array('status'=>'y','info'=>'操作成功'));
			}else{
				$this->ajaxReturn(array('status'=>'n','info'=>'操作失败'));
			}
		}else{
			#显示数据
			$id = I('id',0);
			if($id > 0){
				$row = $db->find($id);
			}else{
				#添加情况,默认显示
				$row['status'] = 1;
			}
			$this->assign($row);

			#无极分类
			$list = category_merge($list,0,0,'id');
			$this->assign('list',$list);
			// dump($list);

			$this->display();
		}
	}
	public function rule_del(){
		$id = trim(I('id'),',');
		$db = M('auth_rule');
		#删除
		$b = $db->delete($id);
		if($b){
			$list = $db->select();
			#查询下级
			$ids = explode(',', $id);
			$children = array();
			foreach($ids as $v){
				#查出传来的所有id的子分类
				$tmp = get_children($list,$v,'id');
				#集合
				$children = array_merge($children,$tmp);
			}
			if($children){
				$where['id'] = array('in',$children);
				$db->where($where)->delete();
			}
			$count = $db->count();
			$this->ajaxReturn(array('status'=>'y','info'=>'删除成功^ ^','children'=>$children,'count'=>$count));
		}else{
			$this->ajaxReturn(array('status'=>'n','info'=>'删除失败* *'));
		}
	}

	/* 角色列表 */
	public function role(){
		$db = M('auth_group');
		$list = $db->order('id desc')->select();
		foreach($list as $k=>$v){
			$users = '';
			$uid = M('auth_group_access')->where(array('group_id'=>$v['id']))->getField('uid',true);
			if($uid){
				$admin = M('admin')->where(array('id'=>array('in',$uid)))->getField('username',true);
				$users = implode('，', $admin);
			}
			$list[$k]['users'] = $users;
		}
		#print_r($list);
		$this->assign('list',$list);
		$this->display();
	}
	/* 角色添加/修改 */
	public function role_add(){
		$db = M('auth_group');

		if(IS_POST){
			#提交过程
			$data = I('post.');
			#print_r($data);die;
			#数据处理
			if(empty($data['title'])){
				$this->error('角色名称为空');die;
			}
			$data['rules'] = !empty($data['rules']) ? implode(',',$data['rules']) : '';

			#操作方式
			if($_POST['id'] > 0){
				$b = $db->save($data);
			}else{
				if(M('auth_group')->field('id')->where(array('title'=>$data['title']))->find()){
					$this->error('角色名称不能重复');
				}
				$b = $db->add($data);
			}
			#操作结果
			if($b !== flase){
				$this->ajaxReturn(array('status'=>'y','info'=>'操作成功'));
			}else{
				$this->ajaxReturn(array('status'=>'n','info'=>'操作失败'));
			}
		}else{
			#显示数据
			$id = I('id',0);
			if($id > 0){
				$row = $db->find($id);
				$row['rules'] = !empty($row['rules']) ? explode(',', $row['rules']) : array();
			}else{
				#添加情况,默认显示
				$row = array('status'=>1,'rules'=>array());
			}
			// dump($row);

			#所有的权限(权限菜单) 
			$rule = M('auth_rule')->field('id,title,parent_id')->where(array('status'=>1))->order('sort')->select();
			#重新组装成树状
			$rule = rule_tree($rule);
			// echo '<pre>';
			// print_r($rule);
			$this->assign($row);
			$this->assign('rule',$rule);
			$this->display();
		}
	}
	public function role_del(){
		$id = trim(I('id'),',');
		$db = M('auth_group');
		#删除
		$b = M('auth_group')->delete($id);
		if($b){
			$this->ajaxReturn(array('status'=>'y','info'=>'删除成功^ ^'));
		}else{
			$this->ajaxReturn(array('status'=>'n','info'=>'删除失败* *'));
		}
	}

	public function admin(){
		$db = M('admin');
		$list = $db->order('id desc')->select();
		foreach($list as $k=>$v){
			$role = '';
			$group_id = M('auth_group_access')->where(array('uid'=>$v['id']))->getField('group_id',true);
			if($group_id){
				$group = M('auth_group')->where(array('id'=>array('in',$group_id)))->getField('title',true);
				$rule = implode('，', $group);
			}
			$list[$k]['roles'] = $rule;
		}
		#print_r($list);
		$this->assign('list',$list);
		$this->display();
	}

	public function admin_add(){
		$db = M('admin');

		if(IS_POST){
			
			#提交过程
			$data = I('post.');
			// print_r($data);die;
			$role = I('role');
			if(empty($role)){
				$this->error('必须选择角色');die;
			}
			#数据处理
			/* 添加 */
			if($data['id'] <= 0 && !empty($data['username'])){
				$c = $db->where(array('username'=>$data['username']))->getField('id');
				if($c){
					$this->error('此账号已被使用');die;
				}
			}
			if($data['id'] <= 0 && empty($data['username'])){
				$this->error('账号不能为空');die;
			}
			if($data['id'] <= 0 && empty($data['password'])){
				$this->error('密码不能为空');die;
			}
			if($data['id'] <= 0 && empty($data['nickname'])){
				$this->error('昵称不能为空');die;
			}

			/* 密码不空修改/或添加 */
			if(!empty($data['password'])){
				$data['entry'] = get_rand_str();
				$data['password'] = md5(md5($data['password']).md5($data['entry']));
			}else{
				unset($data['password']);
			}
			// dump($data);die;
			#操作方式
			if($_POST['id'] > 0){
				$b = $db->save($data);
			}else{
				$b = $db->add($data);
			}
			#操作结果
			if($b !== flase){
				/* 配置管理员的角色 */
				$uid = $_POST['id'] > 0 ? $_POST['id'] : $b;
				#清空当前的角色信息
				M('auth_group_access')->where(array('uid'=>$uid))->delete();
				$role_data = array();
				foreach($role as $v){
					$role_data[] = array(
						'uid' => $uid,
						'group_id' => $v
					);
				}
				M('auth_group_access')->addAll($role_data);
				$this->ajaxReturn(array('status'=>'y','info'=>'操作成功'));
			}else{
				$this->ajaxReturn(array('status'=>'n','info'=>'操作失败'));
			}
		}else{
			#显示数据
			$id = I('id',0);
			if($id > 0){
				$row = $db->find($id);
				/* 当前管理员拥有的角色 */
				$row['roles'] = M('auth_group_access')->where(array('uid'=>$id))->getField('group_id',true);
			}else{
				$row['roles'] = array();
			}
			
			/* 角色列表 */
			$role = M('auth_group')->field('id,title')->where(array('status'=>1))->select();

			$this->assign($row);
			$this->assign('role',$role);
			$this->display();
		}
	}
	public function admin_del(){
		$id = trim(I('id'),',');
		$ids = explode(',', $id);
		if(in_array(1,$ids)){
			$this->ajaxReturn(array('status'=>'n','info'=>'不允许删除超级管理员'));die;
		}
		$b = M('admin')->delete($id);
		if($b){
			M('auth_group_access')->where(array('uid'=>array('in',$id)))->delete();
			$this->ajaxReturn(array('status'=>'y','info'=>'删除成功^^'));
		}else{
			$this->ajaxReturn(array('status'=>'n','info'=>'删除失败'));
		}
	}
	public function repeat(){
		//会员重复
		$repeat = I('repeat');
		if(!empty($repeat)){
			$b = M('admin')->where(array('username'=>$repeat))->getField('id');
			if($b){
				$this->ajaxReturn(array('status'=>'n','此账号已经被使用'));
			}
		}
	}
}