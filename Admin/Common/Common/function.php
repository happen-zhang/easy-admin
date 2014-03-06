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
