<?php
$systemConfig = include('Common/Conf/system_config.php');
$menuConfig = include('menu_config.php');
$backupConfig = include('backup_config.php');
$securityConfig = include('security_config.php');
$mailConfig = include('mail_config.php');

$appConfig =  array(
    // 调试页
    // 'SHOW_PAGE_TRACE' =>true,

    // 默认模块和Action
    'MODULE_ALLOW_LIST' => array('Home'),
    'DEFAULT_MODULE' => 'Home',

    // 默认控制器
    'DEFAULT_CONTROLLER' => 'Public',

    // 分页列表数
    'PAGE_LIST_ROWS' => 10,

    // 开启布局
    'LAYOUT_ON' => true,
    'LAYOUT_NAME' => 'Common/layout',

    // error，success跳转页面
    'TMPL_ACTION_ERROR' => 'Common:dispatch_jump',
    'TMPL_ACTION_SUCCESS' => 'Common:dispatch_jump',

    // 菜单项配置
    'MENU' => $menuConfig,
    'BACKUP' => $backupConfig,
    'MAIL' => $mailConfig,

    // 系统保留表名
    'SYSTEM_TBL_NAME' => 'model,models,filed,fileds,admin,admins',
    // 系统保留菜单名
    'SYSTEM_MENU_NAME' => '首页,模型,数据',

    // 文件上传根目录
    'UPLOAD_ROOT' =>  'Public/uploads/',
    // 系统公用配置目录
    'COMMON_CONF_PATH' => WEB_ROOT . 'Common/Conf/'
);

return array_merge($appConfig, $systemConfig, $securityConfig, $mailConfig);
