<?php

// 过滤函数
define('FILTER_NAME', 'filter_config');

// 填充函数
define('FILL_NAME', 'fill_config');

// 配置文件目录路径
define('FUNC_CONF_DIR_PATH', WEB_ROOT . 'Admin/Common/Conf/');

/**
 * 快速文件缓存
 * @param  string $name  缓存文件名
 * @param  mixed  $value 需要保存的值，为''则表示获取数据
 * @param  strign $path  缓存保存目录
 * @return mixed
 */
function fast_cache($name, $value = '', $path = DATA_PATH) {
    // 全局缓存
    static $_cache = array();
    // 文件名称
    $filename = $path . $name . '.php';

    // 缓存数据
    if ('' !== $value) {
        if (is_null($value)) {
            // 删除缓存
            false === strpos($name, '*') ? array_map('unlink', glob($filename))
                                         : unlink($filename);
        } else {
            // 缓存数据
            $dir = dirname($filename);
            if (!is_dir($dir)) {
                // 创建目录
                mkdir($dir, 0777, true);
            }
            $_cache[$name] = $value;

            // 以php的方式写出数据
            $content = "<?php\r\nreturn " . var_export($value, true) . ";\r\n";
            file_put_contents($filename, $content);
            chmod($filename, 0777);
            return ;
        }
    }

    if (isset($_cache[$name])) {
        return $_cache[$name];
    }

    // 获取缓存文件
    if (is_file($filename)) {
        $value = include($filename);
        $_cache[$name] = $value;
        return $_cache[$name];
    }

    return false;
}

/**
 * 注册过滤函数
 * @param  array  $filter
 * @return mixed
 */
function registry_filter(array $filter) {
    fast_cache(FILTER_NAME, $filter, FUNC_CONF_DIR_PATH);
}

/**
 * 注册填充函数
 * @param  array  $fill
 * @return mixed
 */
function registry_fill(array $fill) {
    fast_cache(FILL_NAME, $fill, FUNC_CONF_DIR_PATH);
}

/**
 * 得到过滤函数
 * @return mixed
 */
function get_registry_filter() {
    return fast_cache(FILTER_NAME, '', FUNC_CONF_DIR_PATH);
}

/**
 * 得到填充函数
 * @return mixed
 */
function get_registry_fill() {
    return fast_cache(FILL_NAME, '', FUNC_CONF_DIR_PATH);
}
