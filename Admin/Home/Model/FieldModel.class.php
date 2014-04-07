<?php

namespace Home\Model;

/**
 * FieldModel
 * 字段模型
 */
class FieldModel extends CommonModel {
    /**
     * name
     */
    protected $validateFieldName = array(
        // 字段名为空验证
        array('name', 'require', '字段名称不能为空！', 1, 'regex', 3),
        // 字段名正确性验证
        array('name', 'isAlpha', '只能由"_"、a~z、A-Z组成！', 1, 'callback', 3),
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
}
