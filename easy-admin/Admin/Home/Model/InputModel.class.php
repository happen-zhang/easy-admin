<?php

namespace Home\Model;

/**
 * InputModel
 * 字段模型
 */
class InputModel extends CommonModel {

    protected $validateInput = array(
        // 是否显示
        array('is_show', '0, 1', '是否显示只能为0或1！', 1, 'in', 3),
        // 表单域标签不能为空
        array('label', 'require', '字段标签不能为空！', 1, 'regex', 3),
        // 表单域类型
        array('type', 'text,password,select,radio,checkbox,textarea,editor,file,date,relation_select',
              '无效的表单域类型！', 1, 'in', 3),
        // 表单域长度
        array('width', 'isint', '表单域长度只能是整数！', 2, 'function', 3),
        // 表单域宽度
        array('height', 'isint', '表单域宽度只能是整数！', 2, 'function', 3),
        // 编辑器类型
        array('editor', 'all,simple', '编辑器只能为"all"或"simple"',
              2, 'in', 3),
        // html
        array('html', 'require', 'html代码不能为空！', 1, 'regex', 3),
        // 显示顺序
        array('show_order', 'isint', '表单域显示顺序只能是整数！', 2, 'function', 3)
    );

    protected $_auto = array(
        // label
        array('label', 'htmlspecialchars', 3, 'function'),
        // remark
        array('remark', 'htmlspecialchars', 3, 'function'),
        // 创建时间
        array('created_at', 'time', 1, 'function'),
        // 更新时间
        array('updated_at', 'time', 3, 'function')
    );

    /**
     * 表单域是否可用
     * @param  array   $input Input数组
     * @param  int     $id    需要更新表单域的id
     * @return boolean        是否可用
     */
    public function isValid($input, $id) {
        $validate = array_merge($this->validateInput);
        return $this->validateConditions($validate, $input, $id);
    }
}
