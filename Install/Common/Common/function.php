<?php
include 'helper.php';

/**
 * 得到操作系统信息
 * @return string
 */
function get_system(){  
    $sys = $_SERVER['HTTP_USER_AGENT'];  
    if(stripos($sys, "NT 6.1"))  
       $os = "Windows 7";  
    elseif(stripos($sys, "NT 6.0"))  
       $os = "Windows Vista";  
    elseif(stripos($sys, "NT 5.1"))  
       $os = "Windows XP";  
    elseif(stripos($sys, "NT 5.2"))  
       $os = "Windows Server 2003";  
    elseif(stripos($sys, "NT 5"))  
       $os = "Windows 2000";  
    elseif(stripos($sys, "NT 4.9"))  
       $os = "Windows ME";  
    elseif(stripos($sys, "NT 4"))  
       $os = "Windows NT 4.0";  
    elseif(stripos($sys, "98"))  
       $os = "Windows 98";  
    elseif(stripos($sys, "95"))  
       $os = "Windows 95";  
    elseif(stripos($sys, "Mac"))  
       $os = "Mac";  
    elseif(stripos($sys, "Linux"))  
       $os = "Linux";  
    elseif(stripos($sys, "Unix"))  
       $os = "Unix";  
    elseif(stripos($sys, "FreeBSD"))  
       $os = "FreeBSD";  
    elseif(stripos($sys, "SunOS"))  
       $os = "SunOS";  
    elseif(stripos($sys, "BeOS"))  
       $os = "BeOS";  
    elseif(stripos($sys, "OS/2"))  
       $os = "OS/2";  
    elseif(stripos($sys, "PC"))  
       $os = "Macintosh";  
    elseif(stripos($sys, "AIX"))  
       $os = "AIX";  
    else  
       $os = "未知操作系统";  
    
    return $os;  
}

/**
 * 创建目录
 * @param  string  $path
 * @param  integer $mode
 * @return boolean
 */
function create_dir($path, $mode = 0777) {
    if (is_dir($path)) {
        chmod($path, $mode);
        return true;
    }

    // 得到目录路径
    $path = dir_path($path);
    $dir_name = explode('/', $path);
    $current_dir = '';

    $max = count($dir_name) - 1;
    for ($i = 0; $i < $max; $i++) {
        $current_dir .= $dir_name[$i] . '/';
        if (is_dir($current_dir)) {
            continue;
        }

        mkdir($current_dir, $mode, true);
        chmod($current_dir, $mode);
    }

    return is_dir($path);
}

/**
 * 得到目录路径
 * @param  string $path
 * @return string
 */
function dir_path($path) {
    $path = str_replace('\\', '/', $path);

    if ('/' != substr($path, -1)) {
        $path = $path . '/';
    }

    return $path;
}
