<?php

$config = array(
    // 表单令牌
    'TOKEN_ON' => true,
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
    'NOT_LOGIN_MODULES' => 'Public'
);

// 登录标记
$config['LOGIN_MARKED'] = md5($config['AUTH_TOKEN']);

return $config;
