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

    // 模型管理
    'Models' => array(
        'name' => '模型管理',
        'target' => 'Models/index',
        'sub_menu' => array(
            'Models/index' => '模型列表',
            'Models/show' => '模型信息',
            'Models/add' => '添加模型',
            'Models/edit' => '编辑模型',
        )
    ),

    // 字段管理
    'Fields' => array(
        'name' => '字段管理',
        'target' => 'Fields/edit',
        'sub_menu' => array(
            'Fields/add' => '添加字段',
            'Fields/edit' => '编辑字段'
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
