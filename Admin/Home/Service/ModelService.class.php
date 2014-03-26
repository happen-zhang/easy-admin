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
    public function getPagination($where, $fields,$order,$firstRow,$listRows) {
        $models = parent::getPagination($where, 
                                        $fields,
                                        $order,
                                        $firstRow,
                                        $listRows);
        foreach ($models as $key => $model) {
            // 模型拥有的字段数目
            $models[$key]['fields_count'] = count($model['fields']);
            // 记录行数
            $rows = D('Common')->getTableRows($model['tbl_name']);
            $models[$key]['rows'] = $rows;
        }

        return $models;
    }

    /**
     * 按id得到model数据
     * @param  int     $id
     * @return array
     */
    public function getById($id) {
        $model = $this->getD()->relation(true)->getById($id);
        if (empty($model)) {
            return null;
        }

        $model['fields_count'] = count($model['fields']);
        $model['rows'] = $this->getD()->getTableRows($model['tbl_name']);

        return $model;
    }

    /**
     * 添加模型并创建数据表
     * @param array $model
     * @return array
     */
    public function add($model) {
        $Model = D('Model');
        $model = array_map('trim', $model);
        $model['tbl_name'] = C('DB_PREFIX') . $model['tbl_name'];
        $Model->startTrans();
        $model = $Model->create($model);
        $addStatus = $Model->add($model);
        // 创建数据表
        $createTblStatus = $Model->createTable($model['tbl_name'],
                                               $model['has_pk'],
                                               $model['tbl_engine'],
                                               $model['description']);
        if (false === $addStatus || false === $createTblStatus) {
            $Model->rollback();
            return $this->resultReturn(false);
        }

        $Model->commit();
        return $this->resultReturn(true);
    }

    /**
     * 更新模型并更新数据表
     * @param array $model
     * @return array
     */
    public function update($model) {
        $Model = D('Model');
        $model = array_map('trim', $model);
        $model['tbl_name'] = C('DB_PREFIX') . $model['tbl_name'];

        // 取出旧数据
        $old = $Model->field('tbl_name')->getById($model['id']);

        $Model->startTrans();
        $model = $Model->create($model);
        // 更新数据
        $updateStatus = $Model->save($model);
        // 更新数据表名
        $utnStatus = $Model->updateTableName($old['tbl_name'],
                                             $model['tbl_name']);
        // 更新数据表注释
        $utcStatus = $Model->updateTableComment($model['tbl_name'],
                                                $model['description']);

        if (false === $updateStatus
            || false === $utnStatus
            || false === $utcStatus) {
            $Model->rollback();
            return $this->resultReturn(false);
        }
        $Model->commit();

        return $this->resultReturn(true);        
    }

    /**
     * 删除模型并且删除数据表
     * @param  int     $id 需要删除模型的id
     * @return boolean
     */
    public function delete($id) {
        $Model = D('Model');

        $model = $Model->getById($id);
        if (empty($model)) {
            return $this->resultReturn(false);
        }

        $Model->startTrans();
        // 删除数据表
        $dropStatus = $Model->dropTable($model['tbl_name']);
        // 删除模型数据
        $delStatus = $Model->delete($id);

        if (false === $dropStatus || false === $delStatus) {
            $Modle->rollback();
            return $this->resultReturn(false);
        }

        $Model->commit();
        return $this->resultReturn(true);
    }

    public function getFieldsOfModelById($modelId, $fields = null) {
        return M('Field')->getByModelId($modelId);
    }

    /**
     * 检查模型名称是否可用
     * @param  string $name 模型名称
     * @return array
     */
    public function checkModelName($name) {
        $Model = D('Model');
        $model['name'] = trim($name);
        if ($Model->isValidModelName($model)) {
            return $this->resultReturn(true);
        }

        return $this->errorResultReturn($Model->getError());
    }

    /**
     * 检查数据表名称是否可用
     * @param  string $name 数据表名称
     * @return array
     */
    public function checkTblName($name) {
        $systemTbls = array('model', 'fields', 'admin');
        if (in_array($name, $systemTbls)) {
            return $this->errorResultReturn('不能使用系统保留表名');
        }

        $Model = D('Model');
        $model['tbl_name'] = trim($name);
        // 验证表名是否空
        if (empty($model['tbl_name'])) {
            return $this->errorResultReturn('数据表名不能为空');
        }

        // 验证表名是否已存在
        $model['tbl_name'] = C('DB_PREFIX') . $model['tbl_name'];
        if ($Model->isValidTblName($model)) {
            return $this->resultReturn(true);
        }

        return $this->errorResultReturn($Model->getError());
    }

    /**
     * 检查模型是否可用
     * @param  array $model 需要检查的模型
     * @return array
     */
    public function checkModel($model) {
        $Model = D('Model');

        // 检查表名是否合法
        $model = array_map('trim', $model);
        $resutl = $this->checkTblName($model['tbl_name']);
        if (false === $resutl['status']) {
            return $this->errorResultReturn($resutl['data']['error']);
        }

        if ($Model->isValid($model)) {
            return $this->resultReturn(true);
        }

        return $this->errorResultReturn($Model->getError());
    }

    protected function getModelName() {
        return 'Model';
    }
}
