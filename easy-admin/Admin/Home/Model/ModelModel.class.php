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
        array('name', 'require', '模型名称不能为空！', 1, 'regex', 3),
        // 模型名正确性验证
        array('name', 'isNotExistSpecialChar', '模型名称不能包含特殊字符！',
              1, 'callback', 3),
        // 模型名长度验证
        array('name', '1, 24', '模型名称长度只能少于24个字符！', 1, 'length', 3),
        // 模型名唯一性验证
        array('name', 'uniqueName', '模型名称已经存在！', 1, 'callback', 3),
    );

    /**
     * tbl_name
     */
    protected $validateTblName = array(
        // 数据表名为空验证
        array('tbl_name', 'require', '数据表名称不能为空！', 1, 'regex', 3),
        // 数据表名长度验证
        array('tbl_name', '1, 24', '数据表名称长度只能少于24个字符！',
              1, 'length', 3),
        // 数据表名正确性验证
        array('tbl_name', 'isLower', '数据表名称只能由"_"、a~z组成！',
              1, 'callback', 3),
        // 系统数据表名验证
        array('tbl_name', 'isNotSysTblName', '不能使用系统保留表名！',
              1, 'callback', 3),
        // 数据表名唯一性验证
        array('tbl_name', 'uniqueTblName', '数据表名称已经存在！',
              1 , 'callback', 3),
    );

    /**
     * menu_name
     */
    protected $validateMenuName = array(
        // 菜单名为空验证
        array('menu_name', 'require', '菜单名称不能为空！', 1, 'regex', 3),
        // 菜单名长度验证
        array('menu_name', '1, 16', '菜单名称长度只能少于16个字符！',
              1, 'length', 3),
        // 系统数据菜单名验证
        array('menu_name', 'isNotSysMenuName', '不能使用系统保留菜单名！',
              1, 'callback', 3),
        // 菜单名唯一性验证
        array('menu_name', 'uniqueMenuName', '菜单名称已经存在！',
              1, 'callback', 3),
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
        array('created_at', 'time', 1, 'function'),
        // 更新时间
        array('updated_at', 'time', 3, 'function'),
    );

    public function uniqueName($value) {
        return $this->isUnique('name', $value);
    }

    public function uniqueTblName($value) {
        return $this->isUnique('tbl_name', $value);
    }

    public function uniqueMenuName($value) {
        return $this->isUnique('menu_name', $value);
    }    

    /**
     * 模型名是否可用
     * @param  array  $model Model数组
     * @param  int    $id    需要更新模型的id
     * @return boolean       是否可用
     */
    public function isValidModelName($model, $id) {
        return $this->validateConditions($this->validateModelName, $model,$id);
    }

    /**
     * 数据表名是否可用
     * @param  array   $model Model数组
     * @param  int     $id   需要更新模型的id
     * @return boolean        是否可用
     */
    public function isValidTblName($model, $id) {
        return $this->validateConditions($this->validateTblName, $model, $id);
    }

    /**
     * 菜单名是否可用
     * @param  array  $model Model数组
     * @param  int    $id    需要更新模型的id
     * @return boolean       是否可用
     */
    public function isValidMenuName($model, $id) {
        return $this->validateConditions($this->validateMenuName, $model, $id);
    }

    /**
     * 模型是否可用
     * @param  array   $model Model数组
     * @param  int     $id    需要更新模型的id
     * @return boolean        是否可用
     */
    public function isValid($model, $id) {
        $validate = array_merge($this->validateModelName,
                                $this->validateTblName,
                                $this->validateMenuName,
                                $this->validateIsInner,
                                $this->validateHasPk,
                                $this->validateTblEngine);
        return $this->validateConditions($validate, $model, $id);
    }

    protected function preUpdate($model, $id) {
        $this->setUpdateSession('update_id', $id);
    }

    protected function afterUpdate($model, $id) {
        $this->unsetUpdateSession('update_id');
    }

    /**
     * 名称是否只包含_、小写字母
     * @param  string $name 需要检查的名称
     * @return boolean      是否有效的名称       
     */
    protected function isLower($name) {
        if (preg_match("/^[a-z_]+$/", $name)) {
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

    /**
     * 检查是否系统保留数据表名称
     * @param  string  $tblName
     * @return boolean
     */
    protected function isNotSysTblName($tblName) {
        $tblNames = explode(',', C('SYSTEM_TBL_NAME'));
        // 带前缀的系统表名
        $prefixTblNames = array();
        $prefix = C('DB_PREFIX');
        foreach ($tblNames as $name) {
            $prefixTblNames[] = $prefix . $name;
        }

        if (in_array($tblName, $tblNames)
            || in_array($tblName, $prefixTblNames)) {
            return false;
        }

        return true;
    }

    /**
     * 检查是否系统保留菜单名称
     * @return boolean
     */
    protected function isNotSysMenuName($tblName) {
        $menuNames = explode(',', C('SYSTEM_MENU_NAME'));

        if (in_array($tblName, $menuNames)) {
            return false;
        }

        return true;
    }
}
