<?php

namespace Home\Model;

/**
 * FieldModel
 * 字段模型
 */
class FieldModel extends CommonModel {
    const UPDATE_SESSION_KEY = 'ea_record_update'; 

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

    public function uniqueName($value) {
        return $this->isUnique('name', $value);
    }  

    public function isUnique($fieldName, $value) {
        $where = array($fieldName => $value, 'model_id' => $this->getUpdateSession('model_id'));
        if ($this->getUpdateSession('update_id')) {
            $where['id'] = array('neq', $_SESSION['update_id']);
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
        return $this->validateConditions($this->validateFieldName, $field,$id);
    }

    /**
     * 验证条件
     * @param  array   $conditions 验证条件
     * @param  array   $field      Field数组
     * @param  int     $id         需要更新字段的id
     * @return boolean             是否可用
     */
    private function validateConditions(array $conditions, $field, $id) {
        $this->setUpdateSession('update_id', $id);
        $this->setUpdateSession('model_id', $field['model_id']);

        $result =  $this->validate($conditions)->create($field);

        $this->unsetUpdateSession('update_id');
        $this->unsetUpdateSession('model_id');

        return $result;
    }

    /**
     * 设置更新外键或者id
     * @param String $key
     * @param  mixed $value
     * @return
     */
    protected function setUpdateSession($key, $value) {
        if (isset($key) && !is_null($key) && !is_null($value)) {
            $_SESSION[self::UPDATE_SESSION_KEY][$key] = $value;
        }
    }

    /**
     * 得到更新外键或者id的值
     * @param  String $key
     * @return
     */
    protected function getUpdateSession($key) {
        return $_SESSION[self::UPDATE_SESSION_KEY][$key];
    }

    /**
     * 销毁session
     * @param String $key
     * @return
     */
    protected function unsetUpdateSession($key) {
        unset($_SESSION[self::UPDATE_SESSION_KEY][$key]);
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
