字符型

char(m) 定长   a  0-255

varchar(m)变长  a 0-65535 

unique 唯一

tinyint 整型

smallint 短整型

unsigned 无符号

primary 主键

auto_increment 自增长



create database oajimohu charset utf8;



create table oa_admin(

id smallint unsigned primary key auto_increment,

username varchar(30) not null unique comment '账号，唯一',

password char(32) not null default '' comment '密码',

entry char(32) not null default '' comment '随机数',

nickname varchar(50) not null default '' comment '昵称',

login_num int not null default 0 comment '登录次数',

login_ip varchar(30) not null default '' comment '登录ip',

login_time int not null default 0 comment '登录时间'

)engine=myisam charset=utf8;



#菜单权限

create table oa_auth_rule(

id smallint unsigned primary key auto_increment,

name varchar(80) not null default '' comment '菜单控制器',

tittle varchar(20) not null default '' comment '菜单名称',

status smallint not null default 1 comment '状态，1为正常状态',

parent_id smallint not null default 0 comment '父级id',

sort smallint not null default 0 comment '排序'

)engine=myisam charset=utf8;





#组员权限

create table oa_auth_group(

id smallint unsigned primary key auto_increment,

tittle varchar(80) not null default '' comment '组员名',

status smallint not null default 1 comment '状态，1为正常状态',

rules varchar(200) not null default '' comment '权限内容',

note varchar(150) not null default '' comment '备注'

)engine=myisam charset=utf8;



#团队，组员与admin链接

create table oa_auth_group_access(

id smallint unsigned primary key auto_increment,

uid smallint not null default 0 comment 'admin id',

group_id smallint not null default 0 comment '组id'

)engine=myisam charset=utf8;



#网站基本设置

create table oa_config(

id smallint unsigned primary key auto_increment,

name varchar(50) not null default 0 comment '名称',

value varchar(200) not null default 0 comment '值',

type varchar(10) not null default '' comment '类型'

)engine=myisam charset=utf8;



#6e0dfc92a2de28a91116daaf61a354f9



create table jy_type_api(

id int unsigned primary key auto_increment,

api_name varchar(100) not null default '',

api varchar(200) not null default '',

type varchar(200) not null default '',

add_time int not null default 0

)engine=myisam charset=utf8;



#股票代码

create table jy_shares(

id int unsigned primary key auto_increment,

shares varchar(100) not null default '',

name varchar(200) not null default '', 

type varchar(200) not null default '',

code varchar(100) not null default '',

add_time int not null default 0

)engine=myisam charset=utf8;



create table jy_order(

id int unsigned primary key auto_increment,

uid char(10) not null,

gpname varchar(100) not null default '' comment '股票名称',

code varchar(100) not null default '' comment '股票代码',

gpnum int not null default 0 comment '购买几手', 

lastestpri int not null default 0 comment '最新价格' , 

type smallint not null default 0 comment '买卖',

gptype char(10) not null default '' comment '股票类型：港股,美股',

total_price int not null default 0,

state smallint not null default 0 comment '0,1已出售',

add_time int not null default 0

)engine=myisam charset=utf8;



create table jy_user(

id int unsigned primary key auto_increment,

username varchar(30) not null unique,

password char(32) not null default '',

entry char(32) not null default '',

nickname varchar(50) not null default '',

money int not null default 0 comment '扩大1000',

area_id smallint not null default 0,

add_time int not null default 0

)engine=myisam charset=utf8;



#区域

create table jy_area(

id int unsigned primary key auto_increment,

admin_id varchar(50) not null default '',

area_name varchar(30) not null unique,

add_time int not null default 0

)engine=myisam charset=utf8;



#用户支付记录

create table jy_user_payment_log(

id int unsigned primary key auto_increment,

uid varchar(50) not null default '',

money int not null default 0,

status smallint not null default 0 comment '支付状态0未付款3支付完成',

start_time int not null default 0 comment '开始支付时间',

end_time int not null default 0 comment '结束支付时间',

add_time int not null default 0 comment '添加时间'

)engine=myisam charset=utf8;



#支付类型

create table jy_pay_type(

id int unsigned primary key auto_increment,



add_time int not null default 0 comment '添加时间'

)engine=myisam charset=utf8;





create table jy_timer_log(

id int unsigned primary key auto_increment,

info varchar(200) not null default '',

add_time int not null default 0

)engine=myisam charset=utf8;



#用户订单结算记录

create table jy_user_order_log(

id int unsigned primary key auto_increment,

uid int not null default 0,

money int not null default 0 comment '最终结算金额',

info varchar(200) not null default '' comment '说明',

add_time int not null default 0

)engine=myisam charset=utf8;





##用户资产表

create table js_assets(

id int unsigned primary key auto_increment,

category_id int not null default 0 comment '资产分类',

store_id int not null default 0 comment '门店id',

name varchar(50) not null default '' comment '资产名称',

conment varchar(200) not null default '' comment '资产内容',

num varchar(50) not null default '' comment '资产数量',

price varchar(50) not null default '' comment '资产价格',

assets_img_1 varchar(100) not null default '' comment '资产图片1',

assets_img_2 varchar(100) not null default '' comment '资产图片2',

assets_img_3 varchar(100) not null default '' comment '资产图片3'

)engine=myisam charset=utf8;



##资产记录表

create table js_assets_log(

id int unsigned primary key auto_increment,

type smallint not null default 0 comment '1:进货，2:出货',

name varchar(50) not null default '' comment '资产名称',

num varchar(20) not null default 0 comment '资产数量',

price varchar(20) not null default 0 comment '资产价格',

admin_id smallint not null default 0 comment '操作人',

add_time int not null default 0 comment '操作时间'

)engine=myisam charset=utf8;



###花材表

#

create table js_flower(

id int unsigned primary key auto_increment,

admin_id smallint not null default 0 comment '添加人id',

img varchar(50) not null default '' comment '图片',

add_mark varchar(500) not null default '' comment '进货说明',

use_mark varchar(500) not null default '' comment '消耗说明',

price varchar(20) not null default 0 comment '价格',

add_time int not null default 0 comment '添加时间'

)engine=myisam charset=utf8;





##资产进货明细

#

create table js_assets_log_list(

id int unsigned primary key auto_increment,

admin_id smallint not null default 0 comment '操作人id',

store_id smallint not null default 0 comment '，门店id',

price varchar(20) not null default '0' comment '进货总价格',

num varchar(20) not null default '0' comment '进货总数量',

img varchar(100) not null default '' comment '单据图片',

assets_log_id varchar(100) not null default '' comment '进货资产ids',

add_time int not null default 0 comment '进货时间'

)engine=myisam charset=utf8;



###愿望清单

create table js_assets_wish(

id smallint unsigned primary key auto_increment,

admin_id smallint not null default 0 comment '添加者id',

store_id smallint not null default 0 comment '门店id',

price varchar(20) not null default '0' comment '总价格',

num varchar(20) not null default '0' comment '总数量',

img varchar(100) not null default '' comment '图片',

assets_id varchar(100) not null default '' comment '资产ids',

add_time int not null default 0 comment '添加时间'

)engine=myisam charset=utf8;





###种植莲藕列表

create table he_lotus(

id smallint unsigned primary key auto_increment,

uid int not null default 0 comment '用户id',

num int not null default 0 comment '种植莲藕数量',

add_time int not null default 0 comment '添加时间'

)engine=myisam charset=utf8;



###采摘动态收入表

create table he_distribution_log(

id smallint unsigned primary key auto_increment,

uid int not null default 0 comment '来源人id',

did int not null default 0 comment '得到收入人id',

type smallint not null default 0 comment '1:采摘静态收入,2:采摘动态收入',

lotus int not null default 0 comment '莲藕数',

peng int not null default 0 comment '莲蓬数',

add_time int not null default 0 comment '添加时间'

)engine=myisam charset=utf8;



##提现表

create table he_get_money(

id smallint unsigned primary key auto_increment,

uid int not null default 0 comment '提现人ID',

type smallint not null default 0 comment '提现状态,0申请提现，',

lotus decimal(10,2) not null default '0.00' comment '提现莲藕数',

lotus_fees decimal(10,2) not null default '0.00' comment '提现手续费',

lotus_rel decimal(10,2) not null default '0.00' comment '实际提现莲藕数',

name varchar(100) not null default 0 comment '名字',

alipay varchar(100) not null default 0 comment '支付宝',

add_time int not null default 0 comment '添加时间'

)engine=myisam charset=utf8;



##商品兑换记录表
create table he_goods_exchange(
id smallint unsigned primary key auto_increment,
uid int not null default 0 comment '兑换人id',
goods_id smallint not null default 0 comment '商品id',
lotus decimal(10,2) not null default '0.00' comment '兑换莲藕数',
num int not null default 0 comment '兑换数量',
add_time int not null default 0 comment '添加时间'
)engine=myisam charset=utf8;


##密保问题表
create table he_safety_question(
id smallint unsigned primary key auto_increment,
conment varchar(200) not null default '' comment '密保问题',
add_time int not null default 0 comment '添加时间'
)engine=myisam charset=utf8;

###
#实名认证表
create table he_member_certified(
id smallint unsigned primary key auto_increment,
user_id int not null default 0 comment '绑定的用户id',
account_no varchar(50) not null default '' comment '银行卡号',
bank_pre_mobile varchar(50) not null default '' comment '银行预留手机号码',
id_card_code varchar(50) not null default '' comment '身份证号码',
name varchar(50) not null default '' comment '持卡人名字',
add_time int not null default 0 comment '添加时间'
)engine=myisam charset=utf8;