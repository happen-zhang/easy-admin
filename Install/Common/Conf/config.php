<?php
$customConfig = include('custom_config.php');

$appConfig = array(
    // 调试页
    // 'SHOW_PAGE_TRACE' =>true,

    // 默认模块
    'MODULE_ALLOW_LIST' => array('Home'),
    'DEFAULT_MODULE' => 'Home',

    // 开启布局
    'LAYOUT_ON' => true,
    'LAYOUT_NAME' => 'Common/layout'    
);

return array_merge($appConfig, $customConfig);
