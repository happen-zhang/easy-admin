<?php
namespace Home\Service;

/**
 * ModelService
 */
class ModelService extends CommonService {

    /**
     * 得到数据模型的详细信息
     * @param  array $where   查询条件
     * @param  string $fields 插叙字段
     * @return array
     */
    public function getModels($where = null, $fields = null) {
        $M = $this->getD()->relation(true);
        if (isset($fields)) {
        	// 确保ralation可用
        	$fields = 'id,' . $fields;
            $M = $M->field($fields);
        }

        if (isset($where)) {
            $M = $M->where($where);
        }

        $models = $M->select();
        foreach ($models as $key => $model) {
        	// 模型拥有的字段数目
            $models[$key]['fields_count'] = count($model['fields']);
            // 记录行数
            $rows = D('Common')->getTableRows($model['tbl_name']);
            $models[$key]['rows'] = $rows;
        }

        return $models;
    }

    public function getFieldsOfModelById($modelId, $fields = null) {
        return M('Field')->getByModelId($modelId);
    }

    protected function getModelName() {
        return 'Model';
    }
}
