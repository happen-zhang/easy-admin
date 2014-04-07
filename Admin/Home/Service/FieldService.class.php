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
    public function checkFieldName($name, $model_id, $id) {
        // 检查字段所属的模型是否存在
        if (M('Model')->where("id = {$model_id}")->count() <= 0) {
            return $this->errorResultReturn('字段对应的模型不存在');
        }

        $Field = $this->getD();
        $field['name'] = trim($name);
        $field['model_id'] = $model_id;
        if ($Field->isValidFieldName($field, $id)) {
            return $this->resultReturn(true);
        }

        return $this->errorResultReturn($Field->getError());
    }

    protected function getModelName() {
        return 'Field';
    }
}
