<?php
// 菜单项配置
return array(
    // 
    'Index' => array(
        'name' => '首页',
        'target' => 'Index/index',
        'sub_menu' => array(
            'Index/index' => '系统信息',
            'Admins/edit' => '修改密码',
            'Cache/index' => '清除缓存',
        )
    ),

    // 数据管理
    'Data' => array(
        'name' => '数据管理',
        'target' => 'Data/backup',
        'sub_menu' => array(
            'Data/backup' => '数据备份',
            'Data/restore' => '数据恢复',
            'Data/unpack' => '数据解压',
            'Data/optimize' => '数据优化'
        )
    ),
);
