<?php
include 'Global/Common/function.php';
include 'helper.php';

/**
 * 格式化的文件大小
 * @param  int $bytes
 * @return string
 */
function bytes_format($bytes) {
	// 单位
    $uint = array(' B', ' KB', ' MB',
    	              ' GB', ' TB', ' PB',
    	              ' EB', ' ZB', ' YB');

    // bytes的对数
    $log_bytes = floor(log($bytes, 1024));
    return round($bytes / pow(1024, log_bytes), 2) . $uint[$log_bytes];
}

/**
 * 生成随机码
 * @param  int $length
 * @param  int $type
 * @return string
 */
function rand_code($length, $type) {
    $rand_factor = array("0123456789",
                        "abcdefghijklmnopqrstuvwxyz",
                        "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
                        "~@#$%^&*(){}[]|");

    if (($type < 0 && $type != -1) || $type > 4) {
        $type = 0;
    }

    if (0 == $type) {
        array_pop($rand_factor);
        $rand_src = implode("", $rand_factor);
    } else if (-1 == $type) {
        $rand_src = implode("", $rand_factor);
    } else {
        $rand_src = $rand_factor[$type];
    }

    $code = '';
    $count = strlen($rand_src) - 1;
    for ($i = 0; $i < $length; $i++) {
        $code .= $rand_src[rand(0, $count)];
    }

    return $code;    
}
