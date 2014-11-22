<?php
include 'helper.php';

/**
 * 得到操作系统信息
 * @return string
 */
function get_system() {
    $sys = $_SERVER['HTTP_USER_AGENT'];

    if (stripos($sys, "NT 6.1")) {
        $os = "Windows 7";
    } else if (stripos($sys, "NT 6.0")) {
        $os = "Windows Vista";
    }  else if (stripos($sys, "NT 5.1")) {
        $os = "Windows XP";
    } else if (stripos($sys, "NT 5.2")) {
        $os = "Windows Server 2003";
    } else if (stripos($sys, "NT 5")) {
        $os = "Windows 2000";
    } else if (stripos($sys, "NT 4.9")) {
        $os = "Windows ME";  
    } else if (stripos($sys, "NT 4")) {
        $os = "Windows NT 4.0";
    } else if (stripos($sys, "98")) {
        $os = "Windows 98";
    } else if (stripos($sys, "95")) {
        $os = "Windows 95";
    } else if (stripos($sys, "Mac")) {
        $os = "Mac";
    } else if (stripos($sys, "Linux")) {
        $os = "Linux";
    } else if (stripos($sys, "Unix")) {
        $os = "Unix";
    } else if (stripos($sys, "FreeBSD")) {
        $os = "FreeBSD";  
    } else if (stripos($sys, "SunOS")) {
        $os = "SunOS"; 
    } else if (stripos($sys, "BeOS")) {
        $os = "BeOS";  
    } else if (stripos($sys, "OS/2")) {
        $os = "OS/2";
    } else if (stripos($sys, "PC")) {
        $os = "Macintosh";  
    } else if(stripos($sys, "AIX")) {
        $os = "AIX";
    } else {
        $os = "未知操作系统";
    }
    
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

/**
 * 创建数据库
 * @param  string $db_name
 * @param  handler $connection
 * @return boolean
 */
function create_database($db_name, $connection) {
    $sql = "CREATE DATABASE IF NOT EXISTS `{$db_name}` "
           . "DEFAULT CHARACTER SET utf8;";

    return mysql_query($sql, $connection);
}

/**
 * 分割sql文件
 * @param  string $sql
 * @param  string $table_prefix
 * @return array
 */
function sql_split($sql, $table_prefix) {
    $default_prefix = C('DEFAULT_TABLE_PREFIX');
    if ($table_prefix != $default_prefix) {
        // 替换默认表前缀
        $sql = str_replace($default_prefix, $table_prefix, $sql);
    }

    // 修改表的字符集为utf8
    $pattern = "/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/";
    $sql = preg_replace($pattern, "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql);

    // 回车转换为换行
    $sql = str_replace("\r", "\n", $sql);

    // 分割为每一条sql
    $queries = explode(";\n", trim($sql));
    unset($sql);

    $result = array();
    foreach ($queries as $key => $query) {
        $query = explode("\n", trim($query));
        // 删除空行
        $query = array_filter($query);

        $result[$key] = '';
        foreach ($query as $item) {
            $temp = substr($item, 0, 1);
            if ($temp != '#' && $temp != '-') {
                $result[$key] .= $item;
            }
        }
    }

    return $result;
}

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
