<?php
namespace Home\Service;

/**
 * FieldService
 */
class FieldService extends CommonService {
    const INDEX_PREFIX = 'idx_';
    const UNIQUE_PREFIX = 'uniq_';

    /**
     * 添加字段
     * @param  array Field数组
     * @return array
     */
    public function add($field) {
        $model = M('Model')->getById($field['model_id']);
        $Field = $this->getD();

        $Field->startTrans();
        $field = $Field->create($field);
        // 插入数据
        $status = $Field->add($field);
        $id = $Field->getLastInsID();
        // 添加字段
        $ac = $Field->addColumn($model['tbl_name'],
                                $field['name'],
                                $field['type'],
                                $field['length'],
                                $field['value'],
                                $field['comment']);

        // 字段索引
        $idxn = self::INDEX_PREFIX . $field['name'];
        if (isset($field['is_index']) && 1 == $field['is_index']) {
            $ai = $Field->addIndex($model['tbl_name'], $field['name'], $idxn);
        }

        // 唯一索引
        $uniqn = self::UNIQUE_PREFIX . $field['name'];
        if ('' == $field['value']
            && isset($field['is_unique'])
            && 1 == $field['is_unique']) {
            $au = $Field->addUnique($model['tbl_name'], $field['name'],$uniqn);
        }

        if (false === $status
            || false === $ac
            || false === $ai
            || false === $au) {
            // 删除插入数据
            $Field->where("id = {$id}")->delete();
            // 删除字段
            $Field->dropColumn($model['tbl_name'], $field['name']);

            $Field->rollback();
            return $this->resultReturn(false);
        }

        $Field->commit();
        return $this->resultReturn(true, array('id' => $id));
    }

    /**
     * 更新字段
     * @param  array $field
     * @return array
     */
    public function update($field) {
        if (!$this->existField($field['id'])) {
            return $this->resultReturn(false);
        }

        $Field = $this->getD();
        $old = $Field->getById($field['id']);

        // 得到数据表名称
        $model = M('Model')->field('tbl_name')->getById($field['model_id']);

        $Field->startTrans();
        // 更新field
        $field = $Field->create($field);
        $status = $Field->save($field);

        // name
        if ($field['name'] != $old['name']
            || $field['type'] != $old['type']
            || $field['length'] != $old['length']) {
            $Field->alterColumnAttr($model['tbl_name'],
                                            $old['name'],
                                            $field['name'],
                                            $field['type'],
                                            $field['length'],
                                            $field['comment']);
        }

        // value
        if ($field['value'] != $old['value']) {
            $Field->alterColumnValue($model['tbl_name'],
                                             $field['name'],
                                             $field['value']);
        }

        // 先删除索引，再进行添加
        $oidxn = self::INDEX_PREFIX . $old['name'];
        if ($field['is_index'] != $old['is_index']
            && 0 == $field['is_index']) {
            $Field->dropIndex($model['tbl_name'], $oidxn);
        }

        $ouniqn = self::UNIQUE_PREFIX . $old['name'];
        if ($field['is_unique'] != $old['is_unique']
            && 0 == $field['is_unique']) {
            $Field->dropIndex($model['tbl_name'], $ouniqn);
        }

        $idxn = self::INDEX_PREFIX . $field['name'];
        if ($field['is_index'] != $old['is_index']
            && 1 == $field['is_index']) {
            $Field->dropIndex($model['tbl_name'], $oidxn);
            $Field->addIndex($model['tbl_name'], $field['name'], $idxn);
        }

        $uniqn = self::UNIQUE_PREFIX . $field['name'];
        if ($field['is_unique'] != $old['is_unique']
            && 1 == $field['is_unique']) {
            $Field->addIndex($model['tbl_name'], $field['name'], $uniqn);
            $Field->dropIndex($model['tbl_name'], $ouniqn);
        }

        if (false === $status) {
            $Field->alterColumnAttr($model['tbl_name'],
                                    $field['name'],
                                    $old['name'],
                                    $old['type'],
                                    $old['length'],
                                    $old['comment']);
            $Field->alterColumnValue($model['tbl_name'],
                                     $old['name'],
                                     $old['value']);
            (1 == $old['is_index']) ?
                  $Field->addIndex($model['tbl_name'], $old['name'], $oidxn)
                  : $Field->dropIndex($model['tbl_name'], $idxn);

            $ouniqn = self::UNIQUE_PREFIX . $old['name'];
            (1 == $old['is_unique']) ?
                  $Field->addUnique($model['tbl_name'], $old['name'], $ouniqn)
                  : $Field->dropIndex($model['tbl_name'], $uniqn);

            $Field->rollback();
            return $this->resultReturn(false);
        }

        $Field->commit();
        return $this->resultReturn(true);
    }

    /**
     * 删除字段
     * @param  int   $id 需要删除字段的id
     * @return array
     */
    public function delete($id) {
        if (!isset($id) || !$this->existField($id)) {
            return resultReturn(false);
        }

        $Field = $this->getD();
        $old = $Field->getById($id);
        $model = M('Model')->field('tbl_name')->getById($old['model_id']);

        $Field->startTrans();
        $status = $Field->where("id={$old['id']}")->delete();
        // 删除表中的字段
        $dcs = $Field->dropColumn($model['tbl_name'], $old['name']);

        if (false === $status || false === $dcs) {
            $Field->addColumn($model['tbl_name'],
                              $old['name'],
                              $old['type'],
                              $old['length'],
                              $old['value'],
                              $old['comment']);

            $Field->rollback();
            return $this->resultReturn(false);
        }

        $Field->commit();
        return $this->resultReturn(true);
    }

    /**
     * 切换是否列表显示状态
     * @param  int     $id 字段id
     * @return boolean
     */
    public function toggleListShow($id) {
        $field = $this->getM()->getById($id);

        $field['is_list_show'] = $field['is_list_show'] ? 0 : 1;

        return $this->getM()->save($field);
    }

    /**
     * 检查字段名称是否可用
     * @param  string     $name 字段名称
     * @param     int $model_id 模型id
     * @param     int       $id 需要更新字段的id
     * @return  array
     */
    public function checkFieldName($name, $modelId, $id) {
        if (!D('Model', 'Service')->existModel($modelId)) {
            return $this->errorResultReturn('字段对应的模型不存在！');
        }

        $Field = $this->getD();
        $field['name'] = trim($name);
        $field['model_id'] = $modelId;
        if ($Field->isValidFieldName($field, $id)) {
            return $this->resultReturn(true);
        }

        return $this->errorResultReturn($Field->getError());
    }

    /**
     * 检查字段标签是否可用
     * @param  string     $name 字段名称
     * @param     int  $modelId 模型id
     * @param     int       $id 需要更新字段的id
     * @return  array
     */
    public function checkFieldComment($comment, $modelId, $id) {
        if (!D('Model', 'Service')->existModel($modelId)) {
            return $this->errorResultReturn('字段对应的模型不存在！');
        }

        $Field = $this->getD();
        $field['comment'] = trim($comment);
        $field['model_id'] = $modelId;
        if ($Field->isValidFieldComment($field, $id)) {
            return $this->resultReturn(true);
        }

        return $this->errorResultReturn($Field->getError());
    }

    /**
     * 检查字段是否可用
     * @param  array $field Field数组
     * @param    int    $id 需要更新field的id
     * @return mixed
     */
    public function checkField(&$field, $id) {
        // 字段类型约束验证
        $result = $this->checkTypeConstraint($field);
        if (!$result['status']) {
            return $result;
        }

        if (!D('Model', 'Service')->existModel($field['model_id'])) {
            return $this->errorResultReturn('字段对应的模型不存在！');
        }

        $Field = $this->getD();
        if ($Field->isValid($field, $id)) {
            return $this->resultReturn(true);
        }

        return $this->errorResultReturn($Field->getError());
    }

    /**
     * 检查字段type相关的value、length约束
     * @param  array $field
     * @return array
     */
    public function checkTypeConstraint(&$field) {
        switch ($field['type']) {
            case 'CHAR':
            case 'VARCHAR':
                $field['length'] = $field['length']['intchar'];
                if (!isset($field['length']) || empty($field['length'])) {
                    return $this->errorResultReturn('字符串类型长度不能为空！');
                }

                if (!isint($field['length'])) {
                    return $this->errorResultReturn('字符串类型长度只能为整数！');
                }

                break ;

            case 'TINYINT':
            case 'SMALLINT':
            case 'INT':
            case 'BIGINT':
                $field['length'] = $field['length']['intchar'];
                if (!isint($field['length'])) {
                    return $this->errorResultReturn('整数类型长度只能为整数！');
                }

                // 默认值只能为整数
                if (!empty($field['value']) && !isint($field['value'])) {
                    return $this->errorResultReturn('整数型默认值只能为有效的整数！');
                }
                break ;

            case 'FLOAT':
            case 'DOUBLE':
                $realLen = array();
                // 长度
                if (!empty($field['length']['real'])) {
                    if (!isint($field['length']['real'])) {
                        return $this->errorResultReturn('浮点型长度只能为整数！');
                    }
                    $realLen[] = $field['length']['real'];

                    // 精度
                    if (!empty($field['precision'])) {
                        if (!isint($field['precision'])) {
                            return $this->errorResultReturn('浮点型精度只能为整数！');
                        }
                        $realLen[] = $field['precision'];
                        unset($field['precision']);
                    }
                }
                // 数据库浮点数形式
                $field['length'] = implode(',', $realLen);

                // 默认值只能为real
                if (!empty($field['value'])
                    && !is_numeric($field['value'])) {
                    return $this->errorResultReturn('浮点型默认值只能为有效的数字！');
                }
                break ;

            default:
                // mysql支持TEXT长度和默认值
                unset($field['length']);
                unset($field['value']);
                $field['is_index'] = 0;
                $field['is_unique'] = 0;
        }

        unset($field['precision']);
        return $this->resultReturn(true);
    }

    /**
     * 重置field长度
     * @param  array  $field
     * @return
     */
    public function resetLength(array &$field) {
        switch ($field['type']) {
            case 'TINYINT':
            case 'SMALLINT':
            case 'INT':
            case 'BIGINT':
            case 'CHAR':
            case 'VARCHAR':
                $length = $field['length'];
                unset($field['length']);
                $field['length']['intchar'] = $length;
                break ;

            case 'FLOAT':
            case 'DOUBLE':
                $length = explode(',', $field['length']);
                unset($field['length']);
                $field['length']['real'] = $length[0];
                $field['precision'] = $length[1];
                break ;
        }
    }

    /**
     * 检查字段是否存在
     * @param  int     $fieldId
     * @return boolean
     */
    public function existField($fieldId) {
        if ($this->getM()->where("id = {$fieldId}")->count() > 0) {
            return true;
        }

        return false;
    }

    /**
     * 按modelId得到所有字段
     * @param  int $modelId
     * @return array
     */
    public function getByModelId($modelId, $field) {
        return $this->getM()
                    ->field($field)
                    ->where("model_id={$modelId}")
                    ->select();
    }

    /**
     * 按realtion_model得到字段
     * @param  int    $modelId 模型id
     * @param  string $field   需要的字段
     * @return array
     */
    public function getByRelationModel($modelId, $field) {
        return $this->getM()
                    ->field($field)
                    ->where("relation_model={$modelId}")
                    ->select();
    }

    /**
     * 以控制器名得到字段
     * @param  string $ctrlName 控制器名
     * @return array
     */
    public function getByCtrlName($ctrlName) {
        $model = M('Model')->getByTblName(D('Model', 'Service')
                           ->getTblName($ctrlName));

        return $fields = $this->getD()
                              ->relation(true)
                              ->where("model_id={$model['id']}")
                              ->select();
    }

    /**
     * 得到被关联的字段
     * @param  int     $modelId 被关联的模型id
     * @param  string  $fn      被关联的字段名
     * @return array
     */
    public function getRelatedFields($modelId, $fn) {
        $where = array('relation_model' => $modelId, 'relation_field' => $fn);
        return $this->getD()->relation(true)->where($where)->select();
    }

    protected function getModelName() {
        return 'Field';
    }
}
