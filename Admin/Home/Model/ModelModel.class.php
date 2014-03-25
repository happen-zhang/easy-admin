<?php

namespace Home\Model;

/**
 * ModelsModel
 * 数据模型
 */
class ModelModel extends CommonModel {
    // realtions
    protected $_link = array(
        // 一个模型拥有多个字段
        'fields' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'Field',
            'foreign_key' => 'model_id',
        )
    );

    /**
     * name
     */
    protected $validateModelName = array(
        // 模型名为空验证
        array('name', 'require', '模型名称不能为空！', 1, 'regex', 1),
        // 模型名正确性验证
        array('name', 'isNotExistSpecialChar', '模型名称不能包含特殊字符！',
              1, 'callback', 1),
        // 模型名长度验证
        array('name', '1, 24', '模型名称长度只能少于24个字符！', 1, 'length', 1),
        // 模型名唯一性验证
        array('name', 'uniqueName', '模型名称已经存在！', 1, 'callback', 1),
    );

    /**
     * tbl_name
     */
    protected $validateTblName = array(
        // 数据表名为空验证
        array('tbl_name', 'require', '数据表名称不能为空！', 1, 'regex', 1),
        // 数据表名长度验证
        array('tbl_name', '1, 24', '数据表名称长度只能少于24个字符！',
              1, 'length', 1),
        // 数据表名正确性验证
        array('tbl_name', 'isNumLower', '数据表名称只能由_、a~z、0~9组成!',
              1, 'callback', 1),
        // 数据表名唯一性验证
        array('tbl_name', 'uniqueTblName', '数据表名称已经存在！',
              1 , 'callback', 1),
    );

    /**
     * is_inner
     */
    protected $validateIsInner = array(
        // 数据表类型值检查
        array('is_inner', '0, 1', '数据表的类型只能为0或1', 1, 'in', 1)
    );

    /**
     * has_pk
     */
    protected $validateHasPk = array(
        array('has_pk', '0, 1', '生成主键的值只能为0或1', 1, 'in', 1)
    );

    /**
     * tbl_engine
     */
    protected $validateTblEngine = array(
        array('tbl_engine',
              'MyISAM,InnoDB,MEMORY,BLACKHOLE,MRG_MYISAM,ARCHIVE',
              '引擎类型不正确',
              0, 'in', 1)
    );

    /**
     * 字段自动完成
     */
    protected $_auto = array(
        // 创建时间
        array('created_at', 'datetime', 1, 'function'),
        // 更新时间
        array('updated_at', 'datetime', 3, 'function'),
    );

    public function uniqueName($value) {
        return parent::isUnique('name', $value);
    }

    public function uniqueTblName($value) {
        return parent::isUnique('tbl_name', $value);
    }

    /**
     * 模型名是否可用
     * @param  array  $model Model数组
     * @return boolean       是否可用
     */
    public function isValidModelName($model) {
        return $this->validateConditions($this->validateModelName, $model);
    }

    /**
     * 数据表名是否可用
     * @param  array   $model Model数组
     * @return boolean        是否可用
     */
    public function isValidTblName($model) {
        return $this->validateConditions($this->validateTblName, $model);
    }

    /**
     * 模型是否可用
     * @param  array   $model Model数组
     * @return boolean        是否可用
     */
    public function isValid($model) {
        $validate = array_merge($this->validateModelName,
                                $this->validateTblName,
                                $this->validateIsInner,
                                $this->validateHasPk,
                                $this->validateTblEngine);
        return $this->validateConditions($validate, $model);
    }

    /**
     * 验证条件
     * @param  array  $conditions 验证条件
     * @param  array  $model      Model数组
     * @return boolean            是否可用
     */
    private function validateConditions(array $conditions, $model) {
        return $this->validate($conditions)->create($model);
    }

    /**
     * 名称是否只包含_、小写字母和数字
     * @param  string $name 需要检查的名称
     * @return boolean      是否有效的名称       
     */
    protected function isNumLower($name) {
        if (preg_match("/^[a-z0-9_]+$/", $name)) {
            return true;
        }

        return false;
    }

    /**
     * 是否包含特殊字符
     * @param  string  $name 需要检查的名称
     * @return boolean       是否不含特殊字符
     */
    protected function isNotExistSpecialChar($name) {
        if (hasSpecialChar($name)) {
            return false;
        }

        return true;
    }
}
