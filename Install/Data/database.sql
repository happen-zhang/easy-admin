
# 数据表：field 结构信息
DROP TABLE IF EXISTS `ea_field`;
CREATE TABLE `ea_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `model_id` int(11) NOT NULL COMMENT '所属模型id',
  `name` varchar(128) NOT NULL COMMENT '字段名称',
  `comment` varchar(32) NOT NULL COMMENT '字段注释',
  `type` varchar(32) NOT NULL COMMENT '字段类型',
  `length` varchar(16) NOT NULL COMMENT '字段长度',
  `value` varchar(128) NOT NULL COMMENT '字段默认值',
  `is_require` tinyint(4) DEFAULT '0' COMMENT '是否必需',
  `is_unique` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否唯一',
  `is_index` tinyint(4) DEFAULT '0' COMMENT '是否添加索引',
  `is_system` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否系统字段',
  `is_list_show` tinyint(4) NOT NULL DEFAULT '1' COMMENT '列表中显示',
  `auto_filter` varchar(32) NOT NULL COMMENT '自动过滤函数',
  `auto_fill` varchar(32) NOT NULL COMMENT '自动完成函数',
  `fill_time` varchar(16) NOT NULL DEFAULT 'both' COMMENT '填充时机',
  `relation_model` int(11) NOT NULL COMMENT '关联的模型',
  `relation_field` varchar(128) NOT NULL COMMENT '关联的字段',
  `relation_value` varchar(128) NOT NULL COMMENT '关联显示的值',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `order_by` int(11) NOT NULL DEFAULT '0' COMMENT '是否排序字段',
  `sort` varchar(32) NOT NULL DEFAULT 'ASC' COMMENT 'desc 倒序 asc 正序',
  PRIMARY KEY (`id`),
  KEY `fk_field_model` (`model_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='数据模型字段' AUTO_INCREMENT=0 ;

# 数据表：model 结构信息
DROP TABLE IF EXISTS `ea_model`;
CREATE TABLE `ea_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(32) NOT NULL COMMENT '模型名称',
  `tbl_name` varchar(32) NOT NULL COMMENT '数据表名称',
  `menu_name` varchar(32) NOT NULL COMMENT '菜单名称',
  `is_inner` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否为内部表',
  `has_pk` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否包含主键',
  `tbl_engine` varchar(16) NOT NULL DEFAULT 'InnoDB' COMMENT '引擎类型',
  `description` text NOT NULL COMMENT '模型描述',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='数据模型信息' AUTO_INCREMENT=0 ;

# 数据表：input 结构信息
DROP TABLE IF EXISTS `ea_input`;
CREATE TABLE `ea_input` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `field_id` int(11) NOT NULL COMMENT '字段id',
  `is_show` tinyint(4) NOT NULL DEFAULT '0' COMMENT '表单域是否显示',
  `label` varchar(32) NOT NULL COMMENT '表单域标签',
  `remark` varchar(128) NOT NULL COMMENT '表单域域',
  `type` varchar(32) NOT NULL COMMENT '表单域类型',
  `width` int(11) NOT NULL DEFAULT '20' COMMENT '表单域宽度',
  `height` int(11) NOT NULL DEFAULT '8' COMMENT '表单域高度',
  `opt_value` text NOT NULL COMMENT '表单域可选值',
  `value` varchar(128) NOT NULL COMMENT '表单域默认值',
  `editor` varchar(32) NOT NULL COMMENT '编辑器类型',
  `html` text NOT NULL COMMENT '表单域html替换',
  `show_order` int(11) DEFAULT NULL COMMENT '表单域显示顺序',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `fk_field_input` (`field_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='字段表单域信息' AUTO_INCREMENT=0 ;

# 数据表：node 结构信息
DROP TABLE IF EXISTS `ea_node`;
CREATE TABLE `ea_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(11) NOT NULL COMMENT '父节点id',
  `name` varchar(32) NOT NULL COMMENT '节点名称',
  `title` varchar(32) NOT NULL COMMENT '节点标题',
  `level` tinyint(4) NOT NULL COMMENT '节点等级',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '节点状态',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `updated_at` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='节点表' AUTO_INCREMENT=0 ;

# 数据库表：role 结构信息
DROP TABLE IF EXISTS `ea_role`;
CREATE TABLE `ea_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(11) NOT NULL COMMENT '父角色id',
  `name` varchar(32) NOT NULL COMMENT '角色名称',
  `description` text NOT NULL COMMENT '角色描述',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '角色状态',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `updated_at` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='角色表' AUTO_INCREMENT=0 ;

# 数据库表：admin 结构信息
DROP TABLE IF EXISTS `ea_admin`;
CREATE TABLE `ea_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `role_id` int(11) NOT NULL COMMENT '所属角色id',
  `email` varchar(64) NOT NULL COMMENT '登录邮箱',
  `password` varchar(32) NOT NULL COMMENT '登录密码',
  `mail_hash` varchar(36) NOT NULL COMMENT '邮件hash值',
  `remark` text NOT NULL COMMENT '管理员备注信息',
  `is_super` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否超级管理员',
  `is_active` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否启用',
  `last_login_at` int(11) NOT NULL COMMENT '最后登录时间',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `updated_at` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE` (`email`),
  KEY `fk_admin_role` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='管理员表' AUTO_INCREMENT=0 ;

# 数据库表：role_admin 结构信息
DROP TABLE IF EXISTS `ea_role_admin`;
CREATE TABLE `ea_role_admin` (
  `role_id` int(11) NOT NULL COMMENT '角色id',
  `user_id` int(11) NOT NULL COMMENT '管理员id',
  KEY `fk_role_admin` (`role_id`),
  KEY `fk_admin_role` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员权限表';

# 数据库表：access 结构信息
DROP TABLE IF EXISTS `ea_access`;
CREATE TABLE `ea_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL COMMENT '角色id',
  `node_id` int(11) NOT NULL COMMENT '节点id',
  PRIMARY KEY (`id`),
  KEY `fk_role_access` (`role_id`),
  KEY `fk_node_acess` (`node_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='结点权限访问表' AUTO_INCREMENT=0 ;

-- --
-- -- 限制表 `ea_field`
-- --
ALTER TABLE `ea_field`
  ADD CONSTRAINT `fk_field_model` FOREIGN KEY (`model_id`) REFERENCES `ea_model` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --
-- -- 限制表 `ea_input`
-- --
ALTER TABLE `ea_input`
  ADD CONSTRAINT `fk_input_field` FOREIGN KEY (`field_id`) REFERENCES `ea_field` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --
-- -- 限制表 `ea_access`
-- --
ALTER TABLE `ea_access`
  ADD CONSTRAINT `fk_node_acess` FOREIGN KEY (`node_id`) REFERENCES `ea_node` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_role_acess` FOREIGN KEY (`role_id`) REFERENCES `ea_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --
-- -- 限制表 `ea_admin`
-- --
ALTER TABLE `ea_admin`
  ADD CONSTRAINT `fk_admin_role` FOREIGN KEY (`role_id`) REFERENCES `ea_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --
-- -- 限制表 `ea_role_admin`
-- --
ALTER TABLE `ea_role_admin`
  ADD CONSTRAINT `fk_ar` FOREIGN KEY (`user_id`) REFERENCES `ea_admin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ra` FOREIGN KEY (`role_id`) REFERENCES `ea_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

# Role数据

INSERT INTO `ea_role` (`id`, `pid`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 0, '超级管理员', '系统内置超级管理员组，不受权限分配账号限制', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

# 节点数据

INSERT INTO `ea_node` (`id`, `pid`, `name`, `title`, `level`, `status`, `created_at`, `updated_at`) VALUES(1, 0, 'Admin', '后台管理', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(2, 1, 'Index', '首页', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(3, 2, 'index', '查看系统信息', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(4, 2, 'editPassword', '修改密码页面', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(5, 2, 'updatePassword', '更新密码', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(6, 2, 'siteEdit', '编辑站点信息', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(7, 2, 'siteUpdate', '更新站点信息', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(8, 1, 'Cache', '缓存管理', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(9, 8, 'index', '缓存文件列表', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(10, 8, 'delete', '删除缓存文件', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(11, 1, 'Admins', '管理员权限', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(12, 11, 'index', '查看管理员列表', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(13, 11, 'add', '添加管理员', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(14, 11, 'create', '创建管理员', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(15, 11, 'edit', '编辑管理员信息', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(16, 11, 'update', '更新管理员信息', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(17, 1, 'Roles', '角色管理', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(18, 17, 'index', '查看角色列表', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(19, 17, 'add', '添加角色', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(20, 17, 'create', '创建角色', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(21, 17, 'edit', '编辑角色信息', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(22, 17, 'update', '更新角色信息', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(23, 17, 'assignAccess', '编辑角色权限', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(24, 17, 'doAssignAccess', '更新角色权限', 3, 1, 0, 0),(25, 1, 'Nodes', '节点管理', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(26, 25, 'index', '查看节点信息', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(27, 25, 'toggleStatus', '修改节点状态', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(28, 1, 'Models', '模型管理', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(29, 28, 'index', '查看模型列表', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(30, 28, 'show', '查看模型信息', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(31, 28, 'add', '添加模型', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(32, 28, 'create', '创建模型', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(33, 28, 'edit', '编辑模型信息', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(34, 28, 'update', '更新模型信息', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(35, 28, 'delete', '删除模型', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(36, 28, 'checkModelName', '检查模型名称', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(37, 28, 'checkTblName', '检查数据表名称', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(38, 28, 'checkMenuName', '检查菜单名称', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(39, 1, 'Fields', '字段管理', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(40, 39, 'index', '查看字段信息列表', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(41, 39, 'add', '添加字段', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(42, 39, 'create', '创建字段', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(43, 39, 'edit', '编辑字段', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(44, 39, 'update', '更新字段', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(45, 39, 'delete', '删除字段', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(46, 39, 'checkFieldName', '检查字段名称', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(47, 39, 'checkFieldLabel', '检查字段标签', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(48, 1, 'Data', '数据管理', 2, 1, 1, UNIX_TIMESTAMP()),(49, 48, 'backup', '查看数据备份列表', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(50, 48, 'doBackup', '处理数据备份', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(51, 48, 'restore', '查看SQL文件列表', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(52, 48, 'doRestore', '恢复SQL文件', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(53, 48, 'deleteSqlFiles', '删除SQL文件', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(54, 48, 'zipSqlFiles', '打包SQL文件为ZIP', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(55, 48, 'zipList', '查看ZIP文件列表', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(56, 48, 'unzipFiles', '解压ZIP文件', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(57, 48, 'deleteZipFiles', '删除ZIP文件', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(58, 48, 'downloadFile', '下载SQL、ZIP文件', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(59, 48, 'optimize', '查看可优化数据表列表', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(60, 48, 'doOptimize', '优化数据表', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),(61, 39, 'toggleListShow', '切换系统字段在数据列表中的显示', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
