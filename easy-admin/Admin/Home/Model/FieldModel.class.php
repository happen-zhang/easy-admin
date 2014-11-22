<?php

namespace Home\Model;

/**
 * FieldModel
 * 字段模型
 */
class FieldModel extends CommonModel {
    // realtions
    protected $_link = array(
        // 一个field对应一个input
        'input' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'Input',
            'foreign_key' => 'field_id',
        )
    );

    /**
     * name
     */
    protected $validateFieldName = array(
        // 字段名为空验证
        array('name', 'require', '字段名称不能为空！', 1, 'regex', 3),
        // 字段名正确性验证
        array('name', 'isAlpha', '字段名只能由"_"、a~z、A-Z组成！'
              , 1, 'callback', 3),
        // 字段名长度验证
        array('name', '1, 24', '字段名称长度只能少于24个字符！', 1, 'length', 3),
        // 字段名唯一性验证
        array('name', 'uniqueName', '字段名称已经存在！', 1, 'callback', 3),
    );

    /**
     * comment
     */
    protected $validateFieldComment = array(
        // 字段标签为空验证
        array('comment', 'require', '字段标签不能为空！', 1, 'regex', 3),
        // 字段标签长度验证
        array('comment', '1, 24', '字段标签长度只能少于24个字符！', 1, 'length', 3),
        // 字段标签唯一性验证
        array('comment', 'uniqueComment', '字段标签已经存在！', 1, 'callback', 3),
    );

    protected $validateField = array(
        // 字段类型
        array('type', 'CHAR,VARCHAR,TINYINT,SMALLINT,INT,BIGINT,FLOAT,DOUBLE,TEXT,MEDIUMTEXT,LONGTEXT,DATE,DATETIME',
              '非法字段类型！', 1, 'in', 3),
        // 字段长度
        array('length', 'isValidFieldLength', '无效的类型长度！', 2, 'callback',3),
        // 填充时机
        array('fill_time', 'both,insert,update', '自动填充时机类型不正确！',
              1, 'in', 3),
        // 是否必需
        array('is_require', '0, 1', '字段必需值只能为0或1！', 1, 'in', 1),
        // 是否唯一
        array('is_unique', '0, 1', '字段唯一值只能为0或1！', 1, 'in', 1),
        // 是否索引
        array('is_index', '0, 1', '字段索引值只能为0或1！', 1, 'in', 1),
        // 是否列表显示
        array('is_list_show', '0, 1', '列表显示值只能为0或1！', 1, 'in', 1)
    );

    protected $_auto = array(
        // 创建时间
        array('created_at', 'time', 1, 'function'),
        // 更新时间
        array('updated_at', 'time', 3, 'function'),
    );

    public function uniqueName($value) {
        return $this->isUnique('name', $value);
    }

    public function uniqueComment($value) {
        return $this->isUnique('comment', $value);
    }

    /**
     * 重写parent::isUnique
     */
    public function isUnique($fieldName, $value) {
        $where = array($fieldName => $value,
                       'model_id' => $this->getUpdateSession('model_id'));

        if ($this->getUpdateSession('update_id')) {
            $where['id'] = array('neq', $this->getUpdateSession('update_id'));
        }

        if (0 == $this->where($where)->count()) {
            return true;
        }

        return false;
    }

    /**
     * 字段名是否可用
     * @param  array  $field Field数组
     * @param  int    $id    需要更新字段的id
     * @return boolean       是否可用
     */
    public function isValidFieldName($field, $id) {
        return $this->validateConditions($this->validateFieldName,
                                         $field,
                                         $id);
    }

    /**
     * 字段标签是否可用
     * @param  array  $field Field数组
     * @param  int    $id    需要更新字段的id
     * @return boolean       是否可用
     */
    public function isValidFieldComment($field, $id) {
        return $this->validateConditions($this->validateFieldComment,
                                         $field,
                                         $id);
    }

    /**
     * 字段是否可用
     * @param  array   $field Field数组
     * @param  int     $id    需要更新字段的id
     * @return boolean        是否可用
     */
    public function isValid($field, $id) {
        $validate = array_merge($this->validateFieldName,
                                $this->validateFieldComment,
                                $this->validateField);
        return $this->validateConditions($validate, $field, $id);
    }

    protected function preUpdate($field, $id) {
        $this->setUpdateSession('update_id', $id);
        $this->setUpdateSession('model_id', $field['model_id']);
    }

    protected function afterUpdate($field, $id) {
        $this->unsetUpdateSession('update_id');
        $this->unsetUpdateSession('model_id');
    }

    /**
     * 名称是否只包含_、字母
     * @param  string $name 需要检查的名称
     * @return boolean      是否有效的名称
     */
    protected function isAlpha($name) {
        if (preg_match("/^[a-zA-Z_]+$/", $name)) {
            return true;
        }

        return false;
    }

    /**
     * 是否有效的字段长度
     * @param  string  $length
     * @return boolean
     */
    protected function isValidFieldLength($length) {
        if (preg_match("/^[0-9,]+$/", $length)) {
            return true;
        }

        return false;
    }
}
