<?php
	function get_daynum($time){
		if(!empty($time)){
			$tmp_time = time();
			$ret = intval(($tmp_time - $time) / 86400);
		}
		return $ret ? $ret : 0;
	}
	function get_invitation_id($id){
		$ret = M('recommend')->where(array('register_id'=>$id))->getField('invitation_id');
		return $ret ? $ret : 'null';
	}
	
	function get_parent($data,$parent_id=1,$level=1,$pk='register_id'){
		$res = array();
		foreach($data as $v){
			if($v['invitation_id'] == $parent_id){
				$v['level'] = $level;
				$res[] = $v;
				$res = array_merge($res,get_parent($data,$v[$pk],$level+1,$pk));
			}
		}
		return $res;
	}

	function get_child($data,$level){
   	 	foreach($data as $v){
   	 		if($v['level'] == $level){	
   	 			$child_id .= $v['register_id'].',';
   	 		}
	   	}
	   	return $child_id;
	}
	/* 获取自己所有后代 */
	function get_I_user($uid){
		$list = M('recommend')->where(array('invitation_id'=>$uid))->getField('register_id',true);
		return $list;
	}
	//获取荷塘的莲藕数目
	function get_I_hlouts($uid){
		$list = M('generate')->where(array('uid'=>$uid))->select();
        foreach ($list as $v) {
			$list['loutss']+=$v['zeng_lotus_new']+$v['zeng_lotus_old'];
		}
		return $list['loutss'] ? $list['loutss'] : '<span style="color:red;">0</span>';
	}	
	function get_rname($uid){
		$rname = M('user')->where(array('id'=>$uid))->getField('username');
		return $rname ? $rname : '<a style="color:red;">用户不存在</a>';
	}
	function get_rname4($uid){
		$rname = M('user')->where(array('id'=>$uid))->getField('is_active');
		return $rname ? '激活' :'<a style="color:red;">未激活</a>';
	}
	function get_rname2($uid){
		$rname = M('user')->where(array('id'=>$uid))->getField('name');
		return $rname ? $rname : '<a style="color:red;">用户不存在</a>';
	}
	function get_iname3($uid){
		$rname = M('recommend')->where(array('register_id'=>$uid))->getField('invitation_id');
		return $rname ? $rname : '<a style="color:red;">无上级</a>';
	}
	function get_iname4($uid){
		$rname2 = M('generate')->where('uid='.$uid)->count();
		$rname=$rname2.'朵';
		return $rname2 ? $rname : '<a style="color:red;">0朵</a>';
	}
	function get_iname($uid){
		$iname = M('user')->where(array('id'=>$uid))->getField('username');
		return $iname ? $iname : '<a style="color:red;">推荐人不存在</a>';
	}
	function rule_tree($data,$parent_id=0){
		$res = array();
		foreach($data as $v){
			if($v['parent_id'] == $parent_id){
				$v['child'] = rule_tree($data,$v['id']);
				$res[] = $v;
			}
		}
		return $res;
	}
	function category_merge($data,$parent_id=0,$level=0,$pk='cat_id'){
		$res = array();
		foreach($data as $v){
			if($v['parent_id'] == $parent_id){
				$v['level'] = $level;
				$res[] = $v;
				$res = array_merge($res,category_merge($data,$v[$pk],$level+1,$pk));
			}
		}
		return $res;
	}

	function get_children($data,$cat_id,$pk='cat_id'){
		$res = array();
		foreach($data as $v){
			if($v['parent_id'] == $cat_id){
				$res[] = $v[$pk];
				$res = array_merge($res,get_children($data,$v[$pk],$pk));
			}
		}
		return $res;
	}

	##获取商品图片
	function get_goods_img($id){
		$row =M('seedling')->find($id);
		return $row['img']?$row['img']:'null';
	}
    ##获取商品名称
	function get_goods_title($id){
		$row =M('seedling')->find($id);
		return $row['name']?$row['name']:'null';
	}    
function creat_rand($len = 20){
    ### 用户名大小写，L O 0 I 这四个不要。
    $arr = array_merge(range(1,9),range('A', 'Z'));
    foreach ($arr as $k => $v) {
        if( $v == 'O' || $v == 'l'||$v == 'L'||$v == 'I'){
            unset($arr[$k]);
        }
    }
    shuffle($arr);
    ## $len 长度
    $sub_arr = array_slice($arr,0,$len);
    $rand = implode('', $sub_arr);
    return $rand;
}
