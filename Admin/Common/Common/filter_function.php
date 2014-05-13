<?php

// 自定义过滤函数
include('Common/Common/filter_function.php');

/**
* 防sql注入
* @param  string $content
* @return string
*/
function sql_injection($content) {
    if (is_array($content)) {
        foreach ($content as $key => $value) {
            $content[$key] = trim($value);
        }

        if (false == get_magic_quotes_gpc()) {
            foreach ($content as $key => $value) {
                // 添加反斜杠
                $content[$key] = addslashes($value);
            }
        }

        foreach ($content as $key => $value) {
            // 转义%
            $content[$key] = str_replace('%', '\%', $value);
            // 转义_
            $content[$key] = str_replace('_', '\_', $value);
        }
    } else {
        // 去除空格
        $content = trim($content);
        if (false == get_magic_quotes_gpc()) {
            // 添加反斜杠
            $content = addslashes($content);
        }
        // 转义%
        $content = str_replace('%', '\%', $content);
        // 转义_
        $content = str_replace('_', '\_', $content);
    }

    return $content;
}

/**
* 转义sql注入字符
* @param  string $content
* @return string
*/
function strip_sql_injection($content) {
    if (is_array($content)) {
        foreach ($content as $key => $value) {
            $content[$key] = str_replace('&quot;', "'", $value);
            $content[$key] = stripslashes($value);
            $content[$key] = str_replace('\%', '%', $value); // 转义%
            $content[$key] = str_replace('\_', '_', $value); // 转义_
            $content[$key] = stripslashes($value);
        }
    } else {
        $content = str_replace('&quot;', "'", $content);
        $content = stripslashes($content);
        $content = str_replace('\%', '%', $content); // 转义%
        $content = str_replace('\_', '_', $content); // 转义_
        $content = stripslashes($content);
    }

    return $content;
}

/**
* 过滤特殊字符
* @param  string $src
* @return string
*/
function filter_special_chars($src) {
    return sql_injection(htmlspecialchars($src));
}

// 得到已经注册的已定义函数
$customFilter = get_registry_filter();
if (!isset($customFilter) || !is_array($customFilter)) {
    $customFilter = array();
}

$filters = array(
    'sql_injection',
    'strip_sql_injection',
    'filter_special_chars'
);

foreach ($filters as $filter) {
    if (!in_array($filter, $customFilter)) {
        $customFilter[] = $filter;
    }
}

fast_cache(FILTER_NAME, $customFilter, FUNC_CONF_DIR_PATH);
