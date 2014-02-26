<?php
$menu_config = include('menu_config.php');

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

    // 菜单项配置
    'MENU' => $menu_config
);

return array_merge($appConfig);
