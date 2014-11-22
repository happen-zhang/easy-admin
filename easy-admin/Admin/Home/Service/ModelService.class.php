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
     * 得到所有的模型
     * @param  string $fields 查询字段
     * @param  string  $order 排序
     * @return array
     */
    public function getAll($fields, $order) {
        return $this->getPagination(null, $fields, $order, null, null);
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
        $Model = $this->getD();
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
        // 添加系统字段
        $addFieldStatus = $this->addSystemFields($Model->getLastInsID());

        // 生成菜单项
        if (isset($model['is_inner']) && 0 == $model['is_inner']) {
            $this->addMenuItem($model);
        }

        // 生成节点
        $ctrlName = $this->getCtrlName($model['tbl_name']);
        $nodeService = D('Node', 'Service');
        $amns = $nodeService->addModuleNodes($model['menu_name'], $ctrlName);

        if (false === $addStatus
            || false === $createTblStatus
            || false === $addFieldStatus
            || false === $amns) {
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
        $Model = $this->getD();
        $model = array_map('trim', $model);
        $model['tbl_name'] = C('DB_PREFIX') . $model['tbl_name'];

        // 取出旧数据
        $old = $Model->field('tbl_name')->getById($model['id']);

        $Model->startTrans();
        $model = $Model->create($model);
        // 更新数据
        $updateStatus = $Model->save($model);
        // 更新数据表名
        if ($model['tbl_name'] != $old['tbl_name']) {
            $utnStatus = $Model->updateTableName($old['tbl_name'],
                                                 $model['tbl_name']);
        }
        // 更新数据表注释
        if ($model['description'] != $old['description']) {
            $utcStatus = $Model->updateTableComment($model['tbl_name'],
                                                    $model['description']);
        }
        // 更新菜单
        if (($model['menu_name'] != $old['menu_name']
            || $model['tbl_name'] != $old['tbl_name'])
            && 0 == $old['is_inner']) {
            $this->replaceMenuItem($model, $old);
        }

        if (false === $updateStatus
            || false === $utnStatus
            || false === $utcStatus) {
            $Model->rollback();
            // 撤回更新
            if (0 == $old['is_inner']) {
                $this->replaceMenuItem($old, $model);
            }
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
        $Model = $this->getD();

        $model = $Model->getById($id);
        if (empty($model)) {
            return $this->resultReturn(false);
        }

        $ctrlName = $this->getCtrlName($model['tbl_name']);

        $Model->startTrans();
        // 删除数据表
        $dropStatus = $Model->dropTable($model['tbl_name']);
        // 删除模型数据
        $delStatus = $Model->delete($id);
        // 删除菜单项
        $this->delMenuItem($ctrlName);
        // 删除节点
        D('Node', 'Service')->deleteModuleNodes($ctrlName);

        if (false === $dropStatus || false === $delStatus) {
            $Modle->rollback();
            // 还原菜单项
            if (0 == $model['is_inner']) {
                $this->addMenuItem($model);
            }
            return $this->resultReturn(false);
        }

        $Model->commit();
        return $this->resultReturn(true);
    }

    /**
     * 检查模型名称是否可用
     * @param  string $name 模型名称
     * @param  int    $id   需要更新模型的id
     * @return array
     */
    public function checkModelName($name, $id) {
        $Model = $this->getD();
        $model['name'] = trim($name);
        if ($Model->isValidModelName($model, $id)) {
            return $this->resultReturn(true);
        }

        return $this->errorResultReturn($Model->getError());
    }

    /**
     * 检查数据表名称是否可用
     * @param  string $name 数据表名称
     * @param  int    $id   需要更新模型的id
     * @return array
     */
    public function checkTblName($name, $id) {
        $Model = $this->getD();
        $model['tbl_name'] = trim($name);
        // 验证表名是否空
        if (empty($model['tbl_name'])) {
            return $this->errorResultReturn('数据表名不能为空');
        }

        // 验证表名是否已存在
        $model['tbl_name'] = C('DB_PREFIX') . $model['tbl_name'];
        if ($Model->isValidTblName($model, $id)) {
            return $this->resultReturn(true);
        }

        return $this->errorResultReturn($Model->getError());
    }

    /**
     * 检查菜单名称是否可用
     * @param  string $name 菜单名称
     * @param  int    $id   需要更新模型的id
     * @return array
     */
    public function checkMenuName($name, $id) {
        $Model = $this->getD();
        $model['menu_name'] = trim($name);
        if ($Model->isValidMenuName($model, $id)) {
            return $this->resultReturn(true);
        }

        return $this->errorResultReturn($Model->getError());
    }

    /**
     * 检查模型是否可用
     * @param  array $model 需要检查的模型
     * @param  int   $id    需要更新模型的id
     * @return array
     */
    public function checkModel($model, $id) {
        $Model = $this->getD();

        // 检查表名是否合法
        $model = array_map('trim', $model);
        $resutl = $this->checkTblName($model['tbl_name'], $id);
        // // 需要检查的模型id
        // $_SESSION['update_id'] = $model['id'];
        if (false === $resutl['status']) {
            return $this->errorResultReturn($resutl['data']['error']);
        }

        if ($Model->isValid($model, $id)) {
            return $this->resultReturn(true);
        }

        return $this->errorResultReturn($Model->getError());
    }

    /**
     * 检查模型是否存在
     * @param      int $modeId
     * @return boolean
     */
    public function existModel($modeId) {
        if ($this->getM()->where("id = {$modeId}")->count() > 0) {
            return true;
        }

        return false;
    }

    /**
     * 检查模型是否存在指定的字段
     * @param  int  $modelId 模型id
     * @param  int  $fieldId 字段id
     * @return boolean
     */
    public function hasField($modelId, $fieldId) {
        $where = array('model_id' => $modelId, 'id' => $fieldId);

        if (null == M('Field')->where($where)->find()) {
            return false;
        }

        return true;
    }

    /**
     * 添加系统字段：id、created_at、updated_at
     * @param  int     $modelId  模型id
     * @return boolean 是否添加成功
     */
    private function addSystemFields($modelId) {
        $Field = D('Field');
        // id字段
        $id = array('model_id' => $modelId,
                    'name' => 'id',
                    'comment' => '表主键',
                    'type' => 'INT',
                    'is_requier' => 1,
                    'is_unique' => 1,
                    'is_index' => 1,
                    'is_system' => 1);
        $id = $Field->create($id);
        $status = false !== $Field->add($id) ? true : false;

        // created_at updated_at
        $timestamp = array('model_id' => $modelId,
                           'type' => 'INT',
                           'is_index' => 1,
                           'is_system' => 1,
                           'auto_fill' => 'time');
        $timestamp = $Field->create($timestamp);
        // created_at字段
        $timestamp['name'] = 'created_at';
        $timestamp['comment'] = '创建时间';
        $timestamp['fill_time'] = 'insert';
        $status = false !== $Field->add($timestamp) ? true : false;
        // updated_at字段
        $timestamp['name'] = 'updated_at';
        $timestamp['comment'] = '更新时间';
        $timestamp['fill_time'] = 'both';
        $status = false !== $Field->add($timestamp) ? true : false;

        return $status;
    }

    /**
     * 添加菜单项
     * @param array $model
     */
    private function addMenuItem(array $model) {
        $modelLogic = D('Model', 'Logic');
        // 得到模型的控制器名称
        $ctrlName = $this->getCtrlName($model['tbl_name']);
        // 生成菜单项
        $item = $modelLogic->genMenuItem($model['menu_name'], $ctrlName);
        // 添加菜单项
        $menu = $modelLogic->addMenuItem($item);
    }

    /**
     * 删除菜单项
     * @param  string $ctrlName 菜单项对应的控制器名称
     * @return mixed
     */
    private function delMenuItem($ctrlName) {
        return D('Model', 'Logic')->delMenuItem($ctrlName);
    }

    /**
     * 替换菜单项
     * @param  array $model
     * @param  array $old
     * @return array
     */
    private function replaceMenuItem($model, $old) {
        $modelLogic = D('Model', 'Logic');
        $oldCtrlName = $this->getCtrlName($old['tbl_name']);
        $ctrlName = $this->getCtrlName($model['tbl_name']);

        // 生成新菜单项
        $item = $modelLogic->genMenuItem($model['menu_name'], $ctrlName);
        // 替换旧的菜单项
        return $modelLogic->replaceMenuItem($item, $oldCtrlName);
    }

    /**
     * 以数据表名得到控制器名称
     * @param  string $tblName
     * @return string
     */
    public function getCtrlName($tblName) {
        // 去掉表前缀
        $tblName = substr($tblName, strpos($tblName, '_') + 1);
        $tblName = str_replace('_', ' ', $tblName);
        // 单词首字母转为大写
        $tblName = ucwords($tblName);

        return str_replace(' ', '', $tblName);
    }

    /**
     * 以控制器名得到表名称
     * @param  string $ctrlName 控制器名
     * @return string
     */
    public function getTblName($ctrlName) {
        $tblName = '';

        for ($i = 0, $len = strlen($ctrlName); $i < $len; $i++) {
            if (strtoupper($ctrlName[$i]) === $ctrlName[$i]) {
                // 大写字母
                $tblName .= '_' . strtolower($ctrlName[$i]);
                continue ;
            }

            $tblName .= $ctrlName[$i];
        }

        return C('DB_PREFIX') . substr($tblName, 1);
    }

    protected function getModelName() {
        return 'Model';
    }
}
