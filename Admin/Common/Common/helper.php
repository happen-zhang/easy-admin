<?php
/**
 * 为当前所在菜单项样式
 * @param  string $controller_name
 * @param  string $action_name
 * @param  string $style
 * @return string
 */
function activedLink($controller_name, $action_name, $style) {
    if (isset($action_name)
        && (false !== strpos($controller_name, CONTROLLER_NAME))
        && ACTION_NAME == $action_name) {
        return $style;
    }

    if (!isset($action_name)
        && (false !== strpos($controller_name, CONTROLLER_NAME))) {
        return $style;
    }

    return '';
}

/**
 * 得到gravatar头像
 * @param  string $email
 * @return string
 */
function getGravatar($email) {
    return 'https://secure.gravatar.com/avatar/' . md5($email) . '.png';
}
