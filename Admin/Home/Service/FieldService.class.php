<?php
namespace Home\Service;

/**
 * FieldService
 */
class FieldService extends CommonService {
    /**
     * 检查字段名称是否可用
     * @param  string     $name 字段名称
     * @param     int $model_id 模型id
     * @param     int       $id 需要更新字段的id
     * @return  array
     */
    public function checkFieldName($name, $modelId, $id) {
        if (!$this->existModel($modelId)) {
            return $this->errorResultReturn('字段对应的模型不存在');
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
        if (!$this->existModel($modelId)) {
            return $this->errorResultReturn('字段对应的模型不存在');
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
    public function checkField($field, $id) {
        // 字段类型约束验证
        $result = $this->checkTypeConstraint($field);
        if (!$result['status']) {
            return $result;
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
                    return $this->errorResultReturn('字符串长度不能为空');
                }

                if (!isint($field['length'])) {
                    return $this->errorResultReturn('字符串长度不合法');
                }

                break ;

            case 'TINYINT':
            case 'SMALLINT':
            case 'INT':
            case 'BIGINT':
                $field['length'] = $field['length']['intchar'];
                if (!isint($field['length'])) {
                    return $this->errorResultReturn('整数长度不合法');
                }

                // 默认值只能为整数
                if (!empty($field['value']) && isint($field['value'])) {
                    return $this->errorResultReturn('默认值只能为整数');
                }
                break ;

            case 'FLOAT':
            case 'DOUBLE':
                $realLen = array();
                // 长度
                if (!empty($field['length']['real'])) {
                    if (!isint($field['length']['real'])) {
                        return $this->errorResultReturn('浮点型长度不合法');
                    }
                    $realLen[] = $field['length']['real'];

                    // 精度
                    if (!empty($field['precision'])) {
                        if (!isint($field['precision'])) {
                            return $this->errorResultReturn('浮点型精度不合法');
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
                    return $this->errorResultReturn('默认值只能为有效的数字');
                }
                break ;
        }

        return $this->resultReturn(true);
    }

    /**
     * 检查模型是否存在
     * @param      int $modeId
     * @return boolean
     */
    private function existModel($modeId) {
        if (M('Model')->where("id = {$modeId}")->count() > 0) {
            return true;
        }

        return false;
    }

    protected function getModelName() {
        return 'Field';
    }
}
