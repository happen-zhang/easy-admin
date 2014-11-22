<?php

/**
 * 环境检测支持
 * @param  string $info
 * @return string
 */
function current_state_support($info) {
    return "<span class='correct_span'>&radic;</span> {$info}";
}

/**
 * 环境检测不支持
 * @param  string $info
 * @return string
 */
function current_state_unsupport($info) {
    return "<span class='correct_span error_span'>&radic;</span> {$info}";
}
