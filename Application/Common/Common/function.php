<?php
	/**
	 * 获取直推用户
	 * @param string $uid 自己
	 * @param bool array 返回类型
	 * return array
	 */
	function get_parentid($uid,$list){
		$ret = array();
		foreach($list as $v){
			if($uid == $v['register_id']){
				$ret[] = $v['invitation_id'];
				$ret = array_merge($ret,get_parentid($v['invitation_id'],$list));
			}
		}
		return $ret;
	}


	/**
	 * 获取直推用户
	 * @param string $i_id 推荐人id
	 * @param bool $ret 返回类型
	 * return string/num
	 */
	function get_recommend($i_id, $ret=true){
		$recommend =  M('recommend')->where(array('invitation_id'=>$i_id))->getField('register_id',true);
		return $ret ? implode(',', $recommend) : count($recommend);
	}

	function get_recommend2($i_id, $ret=true){
		$recommend =  M('recommend')->where(array('invitation_id'=>$i_id))->getField('register_id',true);
		foreach ($recommend as $k => &$v) {
			$row = M('user')->where(array('id'=>$v,'is_active'=>'1'))->find();
			if($row!=null){
				$recommend2[$k]=$v;
			}
		}
		// dump(count($recommend2));
		return $ret ? implode(',', $recommend2) : count($recommend2);
	}
	/* 荷花的存活时间 */
	function get_life($start_time){
		$ret = intval( ( time() - $start_time ) / 86400 );
		return $ret;
	}


	/* 用户荷花 */
	function get_lotus($id,$type=false){
		$ret = M('generate')->where(array('uid'=>$id))->getField('hid',true);
		return $type ? implode(',', $ret) : count($ret);
	}

	/* 加密 */
	function authcode($string, $operation = 'ENCODE', $key = '', $expiry = 0) {
		// 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
		$ckey_length = 4;

		// 密匙
		$key = md5($key ? $key : 'chr6w&H0HXT'); // AUTH_KEY 项目配置的密钥

		// 密匙a会参与加解密
		$keya = md5(substr($key, 0, 16));
		// 密匙b会用来做数据完整性验证
		$keyb = md5(substr($key, 16, 16));
		// 密匙c用于变化生成的密文
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
		// 参与运算的密匙
		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);
		// 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
		// 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);
		$result = '';
		$box = range(0, 255);
		$rndkey = array();
		// 产生密匙簿
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
		// 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		// 核心加解密部分
		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			// 从密匙簿得出密匙进行异或，再转成字符
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}

		if($operation == 'DECODE') {
			// substr($result, 0, 10) == 0 验证数据有效性
			// substr($result, 0, 10) - time() > 0 验证数据有效性
			// substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性
			// 验证数据有效性，请看未加密明文的格式
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			// 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
			// 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码

			return $keyc.str_replace('=', '', base64_encode($result));
		}
	}

 function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice : $slice;
}



/* 随机字符 */
function get_rand_str($len = 6){
	$arr = array_merge(range(0,9),range('a', 'z'),range('A', 'Z'),array('$','@','#','%','&'));
	shuffle($arr);
	$sub_arr = array_slice($arr,0,$len);
	$ret = implode('', $sub_arr);
	session('entry',$ret);
	return $ret;
}

/* 获取配置 */
function get_config($type,$name){
	$result = array();
	$data = M('config')->where(array('type'=>$type))->select();
	if($data){
		foreach($data as $v){
			if(!empty($name)){
				if($v['name'] == $name){
					$result = $v['value'];
					break;
				}
			}else{
				$result[$v['name']] = $v['value'];
			}
		}
	}
	return $result;
}
/* 配置 */
function get_conf($type='RATE'){
	$conf = C($type);
	return $conf;
}
function get_code($uid){
	$is_uid = M('user')->find($uid);
	if(!empty($is_uid)){
		$conf = get_conf('_CODE_');
		$code = msubstr(md5(sha1($uid).md5($conf['_KEY_'])),0,6);
		return $code;
	}else{
		return false;
	}
}

