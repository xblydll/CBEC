<?php
return array(
	//'配置项'=>'配置值'
	'TMPL_PARSE_STRING'  =>array(
		'__PUBLIC__' => __ROOT__.'/Public/Admin/',
	),
	'RATE' => array(
		'_SHARE_' => 0.005,
		'_MONEY_' => 0.8,
		'_INTEGRAL_' => 0.2,
	),
	//auth权限控制配置
    'AUTH_CONFIG' => array(
        'AUTH_ON'           => true,                      // 认证开关
        'AUTH_TYPE'         => 1,                         // 认证方式，1为实时认证；2为登录认证。
        'AUTH_GROUP'        => 'he_auth_group',        // 用户组数据表名
        'AUTH_GROUP_ACCESS' => 'he_auth_group_access', // 用户-用户组关系表
        'AUTH_RULE'         => 'he_auth_rule',         // 权限规则表
        'AUTH_USER'         => 'he_admin'             // 用户信息表
    ),
    //不需要验证的控制器
    'no_auth_controller' => array('Main','Base'),
);