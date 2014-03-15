# --------------------------------------- 
# Easy-Admin Backup File. 
# Github: http://github.com/happen-zhang/easy-admin 
# Description:当前SQL文件包含了表：ea_admin、ea_common、ea_field的结构信息，表：ea_admin、ea_common、ea_field的数据
# Time: 2014-03-15 15:43:05 
# --------------------------------------- 
# 当前SQL卷标：#1
# --------------------------------------- 


# 数据库表：ea_admin 结构信息
DROP TABLE IF EXISTS `ea_admin`;
CREATE TABLE `ea_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `email` varchar(128) NOT NULL,
  `password` char(32) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='管理员表';

# 数据库表：ea_common 结构信息
DROP TABLE IF EXISTS `ea_common`;
CREATE TABLE `ea_common` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# 数据库表：ea_field 结构信息
DROP TABLE IF EXISTS `ea_field`;
CREATE TABLE `ea_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据模型字段域';


# 数据表：ea_admin 数据信息

INSERT INTO `ea_admin` VALUES ('1','b26129ce-9a1a-a36f-5372-9e8d4d2928fb','root@qq.com','root','2014-03-06 16:47:22','2014-03-06 16:47:22');

# 数据表：ea_common 数据信息

INSERT INTO `ea_common` VALUES ('1');
INSERT INTO `ea_common` VALUES ('2');

# 数据表：ea_field 数据信息
# 没有数据记录

