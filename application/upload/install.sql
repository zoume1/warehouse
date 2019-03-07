create table `files_speed_up` (
  `file_id`     bigint unsigned not null default '0' comment '文件id',

  `create_time` int unsigned comment '创建时间戳',
  `status` tinyint unsigned not null default '0' comment '0 生效， 1 失效',
  `server_id` tinyint unsigned not null default '0' comment '所属服务器',

  `url` varchar(512) not null default '' comment '文件地址',

  primary key (`file_id`)	
)engine=innodb  default charset=utf8 comment '文件加速地址';


