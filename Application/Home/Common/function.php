<?php
function get_parent($id,$level=1){
	$data = M('recommend')->select();
	$ret  = array();
	foreach($data as $v){
		if($v['register_id'] == $id && $v['invitation_id'] != 0){
			$ret[] = $v['invitation_id'];//array($level=>$v['invitation_id']);
			$ret = array_merge($ret,get_parent($v['invitation_id'],$level+1));
		}
	}
	return $ret;
}
function decrypt($account){
	$list = M('user')->select();
	foreach($list as $v){
		if($account == md5(md5($v['id']).md5(KEY))){
			$ret = $v['id'];
			break;
		}
	}
	return $ret ? $ret : false;
}
function get_offspring($data,$uid=0,$level=0,$pk='register_id'){
	$res = array();
	foreach($data as $v){
		//入口
		if($v['invitation_id'] == $uid){
			$v['level'] = $level;
			$res[] = $v;
			$res = array_merge($res,get_offspring($data,$v[$pk],$level+1,$pk));
		}
	}
	return $res;
}

function get_offspring_3($data,$uid=0,$level=0,$pk='register_id'){
	$res = array();
	foreach($data as $v){
		//入口
		if($v['invitation_id'] == $uid){
			$v['level'] = $level;
			$res[] = $v;
			if($level <2){
			$res = array_merge($res,get_offspring_3($data,$v[$pk],$level+1,$pk));
		 }
		}
	}
	return $res;
}

##控制交易，拆分时间不能进入，
#00:00 ~ 03:00 
#不能交易
function open_time(){
    $time = time();
    $today = date("Y-m-d",$time);
    #今日凌晨时间
    $todayZero = strtotime($today);
    #今日九点时间
    $todayNine = $todayZero+3*3600;
    if($todayZero<$time && $time<$todayNine){
        return 1;
    }else{
        return 2;
    }
}


##通过id 获取用户电话号码
function get_user_tel($id){
	$row = M('user')->where('id ='.$id)->find();
	return $row['username'] ? $row['username'] : 'null';
}
##通过id获取用户昵称
function get_user_name($id){
	$row = M('user')->where('id ='.$id)->find();
	return $row['name'] ? $row['name'] : 'null';
}
##通过id 获取用户莲藕数量
function get_user_lotus($id){
	$row = M('user')->where('id ='.$id)->find();
	return $row['lotus'] ? $row['lotus'] : '0';
}

##通过id 获取用户莲蓬数量
function get_user_peng($id){
	$row = M('user')->where('id ='.$id)->find();
	return $row['peng'] ? $row['peng'] : '0';
}
##获取商品名称zpf添加2018-04-08
function get_seedling($id){
	$row = M('seedling')->where('id ='.$id)->find();
	return $row['name'] ? $row['name'] : '/';
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