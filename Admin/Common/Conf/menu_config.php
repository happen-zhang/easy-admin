<?php
// 菜单项配置
return array(
    // 
    'Index' => array(
        'name' => '首页',
        'target' => 'Index/index',
        'sub_menu' => array(
            array('item' => array('Index/index' => '系统信息')),
            array('item' => array('Admins/edit' => '修改密码')),
            array('item' => array('Cache/index' => '清除缓存'))
        )
    ),

    // 模型管理
    'Models' => array(
        'name' => '模型管理',
        'target' => 'Models/index',
        'sub_menu' => array(
            array('item' => array('Models/index' => '模型列表')),
            array('item' => array('Models/add' => '添加模型')),
            array('item' => array('Models/show' => '模型信息'),'hidden' => true),
            array('item' => array('Models/edit' => '编辑模型'),'hidden' => true),
        )
    ),

    // 字段管理
    'Fields' => array(
        'name' => '字段管理',
        'target' => 'Fields/edit',
        'sub_menu' => array(
            array('item' => array('Fields/add' => '添加字段')),
            array('item' => array('Fields/edit' => '编辑字段')),
        )
    ),

    // 数据管理
    'Data' => array(
        'name' => '数据管理',
        'target' => 'Data/backup',
        'sub_menu' => array(
            array('item' => array('Data/backup' => '数据备份')),
            array('item' => array('Data/restore' => '数据导入')),
            array('item' => array('Data/zipList' => '数据解压')),
            array('item' => array('Data/optimize' => '数据优化'))
        )
    ),
);
