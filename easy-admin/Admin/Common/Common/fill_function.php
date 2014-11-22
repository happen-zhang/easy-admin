<?php

// 自定义自动填充函数
include('Common/Common/fill_function.php');

/**
* 生成uuid
* @param  string $prefix
* @return string
*/
function uuid($prefix = '') {
    $str = md5(uniqid(mt_rand(), true));
    $uuid  = substr($str,0,8) . '-';
    $uuid .= substr($str,8,4) . '-';
    $uuid .= substr($str,12,4) . '-';
    $uuid .= substr($str,16,4) . '-';
    $uuid .= substr($str,20,12);

    return $prefix . $uuid;
}

/**
* 生成datetime
* @return string
*/
function datetime() {
    return date('Y-m-d H:i:s');
}

// 得到已经注册的已定义函数
$customFill = get_registry_fill();
if (!isset($customFill) || !is_array($customFill)) {
    $customFill = array();
}

$fill = array(
    'uuid',
    'datetime'
);

foreach ($fill as $item) {
    if (!in_array($item, $customFill)) {
        $customFill[] = $item;
    }
}

fast_cache(FILL_NAME, $customFill, FUNC_CONF_DIR_PATH);
