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
        && CONTROLLER_NAME == $controller_name
        && ACTION_NAME == $action_name) {
        return $style;
    }

    if (!isset($action_name)
        && CONTROLLER_NAME == $controller_name) {
        return $style;
    }

    return '';
}
