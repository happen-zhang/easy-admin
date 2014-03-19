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
    $unit = array(' B', ' KB', ' MB',
                  ' GB', ' TB', ' PB',
                  ' EB', ' ZB', ' YB');

    // bytes的对数
    $log_bytes = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $log_bytes), 2) . $unit[$log_bytes];
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

/**
 * 删除目录或者文件
 * @param  string  $path
 * @param  boolean $is_del_dir
 * @return fixed
 */
function del_dir_or_file($path, $is_del_dir = FALSE) {
    $handle = opendir($path);
    if ($handle) {
        // $path为目录路径
        while (false !== ($item = readdir($handle))) {
            // 除去..目录和.目录
            if ($item != '.' && $item != '..') {
                if (is_dir("$path/$item")) {
                    // 递归删除目录
                    del_dir_or_file("$path/$item", $is_del_dir);
                } else {
                    // 删除文件
                    unlink("$path/$item");
                }
            }
        }
        closedir($handle);
        if ($is_del_dir) {
            // 删除目录
            return rmdir($path);
        }
    }else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return false;
        }
    }
}
