<?php

return array(
    // 作者信息
    'AUTHOR_NAME' => 'happen_zhang',

    // 系统名称
    'SYSTEM_NAME' => 'Easy-Admin后台安装向导',

    // 版本号
    'SYSTEM_VERSION' => '0.0.1',

    // 系统默认的表前缀
    'DEFAULT_TABLE_PREFIX' => 'ea_',

    // 系统安装数据SQL文件位置
    'SYSTEM_SQL_PATH' => WEB_ROOT . 'Install/Data/database.sql',

    // 系统配置文件位置
    'SYSTEM_CONFIG_PATH' => WEB_ROOT . 'Global/Conf/system_config.php',

	// 一下配置目录都是在项目的根目录下
    'WRITABLE_DIRECTORIES' => array(
        '/',
        'Global/Conf',
        'Cache',
        'Data',
        'Install'
    ),
);
