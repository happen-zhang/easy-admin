<?php

$sysConfig = include('Common/Conf/system_config.php');

$config = array(
    // 表单令牌
    'TOKEN_ON' => false,
    'TOKEN_NAME' => '__hash__',
    'TOKEN_TYPE' => 'md5',
    'TOKEN_RESET' => true,

    // 认证token
    'AUTH_TOKEN' => 'eaadmin',
    // 认证mask
    'AUTH_MASK' => 'nimdaae',
    // 登录超时
    'LOGIN_TIMEOUT' => 3600,

    // 不用认证登录的模块
    'NOT_LOGIN_MODULES' => 'Public',

    // 开启权限认证
    'USER_AUTH_ON' => true,
    // 登录认证模式
    'USER_AUTH_TYPE' => 1,
    // 认证识别号
    'USER_AUTH_KEY' => 'mineaad',
    // 超级管理员认证号
    'ADMIN_AUTH_KEY' => 'eaadminae',
    // 游客识别号
    'GUEST_AUTH_ID' => 'guest',
    // 模块名称（不要修改）
    'GROUP_AUTH_NAME' => 'Admin',
    // 无需认证模块
    'NOT_AUTH_MODULE' => 'Public',
    // 需要认证模块
    'REQUIRE_AUTH_MODULE' => '',
    // 认证网关
    'USER_AUTH_GATEWAY' => 'Public/index',
    // 关闭游客授权访问
    'GUEST_AUTH_ON' => false,
    // 管理员模型
    'USER_AUTH_MODEL' => 'Admin',
    // 角色表
    'RBAC_ROLE_TABLE' => $sysConfig['DB_PREFIX'] . 'role',
    // 管理员-角色表
    'RBAC_USER_TABLE' => $sysConfig['DB_PREFIX'] . 'role_admin',
    // 节点表
    'RBAC_NODE_TABLE' => $sysConfig['DB_PREFIX'] . 'node',
    // 节点访问表
    'RBAC_ACCESS_TABLE' => $sysConfig['DB_PREFIX'] . 'access'
);

// 登录标记
$config['LOGIN_MARKED'] = md5($config['AUTH_TOKEN']);

return $config;
