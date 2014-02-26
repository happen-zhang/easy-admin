<?php
// 菜单项配置
return array(
    'Index' => array(
        'name' => '首页',
        'target' => 'Index/index',
        'sub_menu' => array(
            'Index/index' => '系统信息',
            'Admins/edit' => '修改密码',
            'Cache/index' => '清除缓存',
        )
    )
);
