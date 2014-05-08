<?php

$modelMenu = include('model_menu.php');

if (false === $modelMenu) {
    $modelMenu = array();
}

// 菜单项配置
$systemMenu = array(
    //
    'Index' => array(
        'name' => '首页',
        'target' => 'Index/index',
        'sub_menu' => array(
            array('item' => array('Index/index' => '系统信息')),
            array('item' => array('Cache/index' => '清除缓存'))
        )
    ),

    // 数据管理
    'Admins' => array(
        'name' => '管理员权限',
        'target' => 'Admins/index',
        'sub_menu' => array(
            array('item' => array('Admins/index' => '管理员信息')),
            array('item' => array('Admins/rolesIndex' => '角色管理')),
            array('item' => array('Admins/nodesIndex' => '节点管理')),
            array('item' => array('Admins/add' => '添加管理员')),
            array('item' => array('Admins/roleAdd' => '添加角色')),
            array('item' => array('Admins/nodeAdd' => '添加节点')),
            array('item'=>array('Admins/edit'=>'编辑管理员信息'),'hidden'=>true),
            array('item' => array('Admins/roleEdit'=>'编辑角色信息'),
                  'hidden'=>true),
            array('item' => array('Admins/nodeEdit'=>'编辑节点信息'),
                  'hidden'=>true),
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
        'mapping' => 'Models',
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

return array_merge($systemMenu, $modelMenu);
