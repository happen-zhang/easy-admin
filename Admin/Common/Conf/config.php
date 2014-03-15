<?php
$systemConfig = include('Global/Conf/system_config.php');
$menuConfig = include('menu_config.php');
$backupConfig = include('backup_config.php');

$appConfig =  array(
    // 调试页
    'SHOW_PAGE_TRACE' =>true,

    // 默认模块和Action
    'MODULE_ALLOW_LIST' => array('Home'),
    'DEFAULT_MODULE' => 'Home',

    // 默认控制器
    'DEFAULT_CONTROLLER' => 'Public',

    // 分页列表数
    'PAGINATION_NUM' => 20,

    // 开启布局
    'LAYOUT_ON' => true,
    'LAYOUT_NAME' => 'Common/layout',

    // error，success跳转页面
    'TMPL_ACTION_ERROR' => 'Common:dispatch_jump',
    'TMPL_ACTION_SUCCESS' => 'Common:dispatch_jump',

    // 菜单项配置
    'MENU' => $menuConfig,
    'BACKUP' => $backupConfig
);

return array_merge($appConfig, $systemConfig);
