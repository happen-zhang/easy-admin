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

/**
 * 生成input文本框
 * @param  string $name  文本框的name
 * @param  int    $size  文本框大小
 * @param  string $value 文本框默认值
 * @param  string $class css类
 * @return string
 */
function genText($name, $size, $value, $class) {
    return "<input type='text' class='{$class}' "
           . "size='{$size}' name='{$name}' value='{$value}' />";
}

/**
 * 生成input密码框
 * @param  string $name  密码框的name
 * @param  string $size  密码框大小
 * @param  string $value 密码框默认值
 * @param  string $class css类
 * @return string
 */
function genPassword($name, $size, $value, $class) {
    return "<input type='password' class='{$class}' "
           . "size='{$size}' name='{$name}' value='{$value}' />";
}

/**
 * 生成select下拉框
 * @param  string $name    下拉框的name
 * @param  array  $list    下拉框的可选项
 * @param  int    $seleced 默认项
 * @param  string $class   css类
 * @return string
 */
function genSelect($name, array $list, $selected = 0, $class = '') {
    $html = "<select name='{$name}' class='{$class}'>";

    $i = 0;
    foreach ($list as $text => $value) {
        $html .= indent() . "<option value='{$value}' ";
        if ($i == $selected) {
            $html .= " selected='selected' ";
        }
        $html .= ">{$text}</option>";

        $i++;
    }

    $html .= "</select>";

    return $html;
}

/**
 * 生成radio单选框
 * @param  string  $name    单选框的name
 * @param  string  $text    单选框显示文本
 * @param  string  $value   单选框的值
 * @param  boolean $checked 是否选中
 * @param  string $class    css类
 * @return string
 */
function genRadio($name, $text, $value, $checked = false, $class = '') {
    $html = "<input type='radio' name='{$name}' "
            . "value='{$value}' class='{$class}' ";
    if ($checked) {
        $html .= "checked='checked'";
    }
    $html .= " /> {$text} ";

    return $html;
}

/**
 * 生成radio单选框组
 * @param  string  $name    单选框的name
 * @param  array   $list    单选框列表
 * @param  int     $checked 是否选中
 * @param  string  $class   css类
 * @return string
 */
function genRadios($name, array $list, $checked = 0, $class = '') {
    $html = '';

    $i = 0;
    foreach ($list as $text => $value) {
        $html .= $i == $checked ? genRadio($name, $text, $value, true, $class)
                                : genRadio($name, $text, $value);
        $i++;
    }

    return $html;
}

/**
 * 生成checkbox复选框
 * @param  string  $name    复选框的name
 * @param  string  $text    复选框显示文本
 * @param  string  $value   复选框的值
 * @param  boolean $checked 是否选中
 * @param  string  $class   css类
 * @return string
 */
function genCheckbox($name, $text, $value, $checked = false, $class = '') {
    $html = "<input type='checkbox' name='{$name}[]' "
            . "value='{$value}' class='{$class}'";
    if ($checked) {
        $html .= "checked='checked'";
    }
    $html .= " /> {$text} ";

    return $html;
}

/**
 * 生成checkbox复选框组
 * @param  string  $name    复选框的name
 * @param  array   $list    复选框列表
 * @param  string  $checked 是否选中，','隔开
 * @param  string  $class   css类
 * @return string
 */
function genCheckboxs($name, array $list, $checked, $class = '') {
    $html = '';
    $checked = array_filter(explode(',', $checked), function($pos) {
        return !(empty($pos) && 0 !== $pos && '0' !== $pos);
    });

    $i = 0;
    foreach ($list as $text => $value) {
        $html .= in_array($i, $checked) ?
                     genCheckbox($name, $text, $value, true, $class)
                     : genCheckbox($name, $text, $value);
        $i++;
    }

    return $html;
}

/**
 * 生成file文件上传
 * @param  string $name 文件域的名称
 * @return string
 */
function genFile($name, $class = '') {
    return "<input type='file' name='{$name}' class='{$class}' />";
}

/**
 * 生成datepicker
 * @param  string $name  表单域名称
 * @param  string $class css类
 * @return string
 */
function genDate($name, $value, $class = '') {
    $src = __APP__ . '/../Public/javascripts/admin/datepicker/images2/cal.gif';
    $id = rand_code(8);

    return "<input type='text' id='{$id}' "
           . "value='{$value}' class='{$class}' name='{$name}' />"
           . "<img src='{$src}' style='cursor:pointer; margin-left:2px' "
           . "onclick='javascript:NewCssCal(\"{$id}\", \"YYYYMMDD\")'/>";
}

/**
 * 生成textarea文本域
 * @param  string $name        文本域name
 * @param  string $value       文本域value
 * @param  int    $rows        文本域rows
 * @param  int    $cols        文本域cols
 * @param  string $placeholder 文本域holder
 * @param  string $class       css类
 * @return string
 */
function genTextarea($name, $value, $cols, $rows, $placeholder = '', $class) {
    $html = "<textarea name='{$name}' class='{$class}' "
            . "rows='{$rows}' cols='{$cols}' ";
    if (isset($value) && !empty($value)) {
        $html .= ">{$value}</textarea>";
    } else if ('' != $placeholder) {
        $html .= "placeholder='{$placeholder}'></textarea>";
    } else {
        $html .= "></textarea>";
    }

    return $html;
}

/**
 * 生成编辑器
 * @param  string $name   文本域name
 * @param  string $value  文本域value
 * @param  int    $rows   文本域rows
 * @param  int    $cols   文本域cols
 * @param  string $type   编辑器类型
 * @return string
 */
function genEditor($name, $value, $cols, $rows, $type = 'simple') {
    $id = rand_code(8);
    $html = "<textarea name='{$name}' id='{$id}' "
            . "rows='{$rows}' cols='{$cols}' ";

    if ('simple' == $type) {
        $js = "<script type='text/javascript'>$(function(){KindEditor.ready(function(K) {K.create('#{$id}',{resizeType:1,items:['fontname','fontsize','|','forecolor','hilitecolor','bold','italic','underline','removeformat','|','justifyleft','justifycenter','justifyright','insertorderedlist','insertunorderedlist','|','emoticons','image','link'],afterBlur:function(){this.sync();}});});});</script>";
    } else {
        $js = "<script type='text/javascript'>$(function(){KindEditor.ready(function(K) {K.create('#{$id}',{resizeType:1,afterBlur:function(){this.sync();}});});});</script>";
    }

    if (isset($value) && !empty($value)) {
        $html .= ">{$value}</textarea>";
    } else {
        $html .= "></textarea>";
    }

    return $html . $js;
}

/**
 * 缩进
 * @param  integer $space 缩进空格的数量
 * @return string
 */
function indent($space = 4) {
    $indent = '';
    for ($i = 0; $i < $space; $i++) {
        $indent .= ' ';
    }

    return $indent;
}
