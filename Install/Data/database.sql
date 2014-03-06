
# 数据库表：admin 结构信息
DROP TABLE IF EXISTS `ea_admin`;
CREATE TABLE `ea_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `email` varchar(128) NOT NULL,
  `password` char(32) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 COMMENT='管理员表' ;
