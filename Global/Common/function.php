<?php

/**
 * 生成验证码
 * @param  array  $config
 * @return 
 */
function create_verify_code(array $config) {
	$Verify = new \Think\Verify($config);
    return $Verify->entry();
}

/**
 * 检查验证码
 * @param  string $code
 * @param  int $verify_code_id
 * @return boolean
 */
function check_verify_code($code, $verify_code_id) {
	$Verify = new \Think\Verify($config);
    return $Verify->check($code, $verify_code_id);
}
