<?php
namespace Admin\Controller;
use Think\Controller;
class MainController extends BaseController {
    public function index(){
    	$adminid = session('adminid');
    	$admin = M('admin')->find($adminid);
		$this->assign($admin);
		$this->display();
    }
    public function welcome(){
    	$db = M('user');
    	$count['user'] = $db->count();
    	$count['admin'] = M('admin')->count();
    	$count['lotus'] = $db->sum('lotus');
    	$count['peng'] = $db->sum('peng');
    	$count['integral'] = $db->sum('integral');
    	$count['he'] = M('generate')->where(array('state'=>1))->count();

    	$t = time();
		$start_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
		// $end_time = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));

    	$count['day_he'] = M('generate')->where(array('state'=>1,'start_time' => array('gt',$start_time)))->count();
    	//今日兑换的积分
    	$lotus_integral = M('log')->where(array('log_type'=>2,'add_time' => array('gt',$start_time)))->sum('number');
    	$peng_integral = M('log')->where(array('log_type'=>2,'add_time' => array('gt',$start_time)))->sum('number1');
    	$count['integral_day'] = ($lotus_integral + $peng_integral) * 10;
    	//今日用户
    	$count['user_day'] = $db->where(array('add_time' => array('gt',$start_time)))->count();

    	$this->assign('count',$count);
    	$this->display();
    }
}