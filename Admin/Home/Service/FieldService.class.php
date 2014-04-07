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
