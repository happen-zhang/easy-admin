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
     * 添加旧的数据表
     *
        COLUMNS
        当前数据库中当前用户可以访问的每一个列在该视图中占一行。INFORMATION_SCHEMA.COLUMNS 视图以 sysobjects、spt_data type_info、systypes、syscolumns、syscomments、sysconfigures 以及 syscharsets 系统表为基础。

        若要从这些视图中检索信息，请指定完全合格的 INFORMATION_SCHEMA view_name 名称。

        列名	                 数据类型            	描述
        TABLE_CATALOG	     nvarchar(128)	    表限定符。
        TABLE_SCHEMA	     nvarchar(128)	    表所有者。
        TABLE_NAME	        nvarchar(128)	    表名。
        COLUMN_NAME	        nvarchar(128)	    列名。
        ORDINAL_POSITION	smallint	        列标识号。
        COLUMN_DEFAULT	    nvarchar(4000)	    列的默认值。
        IS_NULLABLE	        varchar(3)	        列的为空性。如果列允许 NULL，那么该列返回 YES。否则，返回 NO。
        DATA_TYPE	        nvarchar(128)	    系统提供的数据类型。
        CHARACTER_MAXIMUM_LENGTH	smallint	以字符为单位的最大长度，适于二进制数据、字符数据，或者文本和图像数据。否则，返回 NULL。有关更多信息，请参见数据类型。
        CHARACTER_OCTET_LENGTH	smallint	    以字节为单位的最大长度，适于二进制数据、字符数据，或者文本和图像数据。否则，返回 NULL。
        NUMERIC_PRECISION	    tinyint	        近似数字数据、精确数字数据、整型数据或货币数据的精度。否则，返回 NULL。
        NUMERIC_PRECISION_RADIX	smallint	    近似数字数据、精确数字数据、整型数据或货币数据的精度基数。否则，返回 NULL。
        NUMERIC_SCALE	        tinyint	        近似数字数据、精确数字数据、整数数据或货币数据的小数位数。否则，返回 NULL。
        DATETIME_PRECISION	    smallint	    datetime 及 SQL-92 interval 数据类型的子类型代码。对于其它数据类型，返回 NULL。
        CHARACTER_SET_CATALOG	varchar(6)	    如果列是字符数据或 text 数据类型，那么返回 master，指明字符集所在的数据库。否则，返回 NULL。
        CHARACTER_SET_SCHEMA	varchar(3)	    如果列是字符数据或 text 数据类型，那么返回 DBO，指明字符集的所有者名称。否则，返回 NULL。
        CHARACTER_SET_NAME	    nvarchar(128)	如果该列是字符数据或 text 数据类型，那么为字符集返回唯一的名称。否则，返回 NULL。
        COLLATION_CATALOG	    varchar(6)	    如果列是字符数据或 text 数据类型，那么返回 master，指明在其中定义排序次序的数据库。否则此列为 NULL。
        COLLATION_SCHEMA	    varchar(3)	    返回 DBO，为字符数据或 text 数据类型指明排序次序的所有者。否则，返回 NULL。
        COLLATION_NAME	        nvarchar(128)	如果列是字符数据或 text 数据类型，那么为排序次序返回唯一的名称。否则，返回 NULL。
        DOMAIN_CATALOG	        nvarchar(128)	如果列是一种用户定义数据类型，那么该列是某个数据库名称，在该数据库名中创建了这种用户定义数据类型。否则，返回 NULL。
        DOMAIN_SCHEMA	        nvarchar(128)	如果列是一种用户定义数据类型，那么该列是这种用户定义数据类型的创建者。否则，返回 NULL。
        DOMAIN_NAME	            nvarchar(128)	如果列是一种用户定义数据类型，那么该列是这种用户定义数据类型的名称。否则，返回 NULL。
     * @param $model
     * @return array
     */
    public function add_old_table($model) {
        $Model = $this->getD();
        $model = array_map ( 'trim', $model );

        $new_db = $model ['db_name'];
        $new_table = $model ['tbl_name_old'];
        $model ['tbl_name'] = $model ['db_name'].'.'.$model ['tbl_name_old'];

        $Model->startTrans ();
        $model = $Model->create ( $model );
        $addStatus = $Model->add ( $model );

        $sql = "SELECT `COLUMN_NAME`,DATA_TYPE,COLUMN_COMMENT,COLUMN_TYPE FROM information_schema.columns WHERE table_schema ='".$new_db."' AND table_name = '".$new_table."'";
        $table_attribute = $Model->query ( $sql );
        $modelId = $Model->getLastInsID ();

        $fieldService = D ( 'Field', 'Service' );
        $inputService = D('input', 'Service');

        //查询此表主键
        $qu_key['name'] = $model ['tbl_name'];

        foreach ( $table_attribute as $ke => $val ) {
            $column_name = $val ['COLUMN_NAME'];
            $comment = $val ['COLUMN_COMMENT'];
            $model_name = $model ['tbl_name'];
            $length = "";
            if(preg_match('/\d+/',$val['COLUMN_TYPE'], $arr)){
                $length = $arr[0];
            }

            $field = array (
                'model_id' => $modelId,
                'name' => $column_name,
                'comment' => $comment,
                'type' => $val ['DATA_TYPE'],
                'length'=> $length,
                'is_requier' => 0,
                'is_unique' => 0,
                'is_index' => 0,
                'is_system' => 0,
                'auto_fill' =>'',
                'created_at' => time (),
                'updated_at' => time (),
                'is_old' => 1
            );

            $addFieldStatus = $fieldService->just_add_field ( $field );

            $input = array (
                'field_id' => $addFieldStatus ['data'] ['id'],
                'is_show' => 1,
                'label' => $column_name,
                'remark' => $comment,
                'type' => 'text',
                'width' => 20,
                'height' => 0,
                'opt_value' => '',
                'value' => '',
                'editor' => 'all',
                'html' => "<input type='text' class='input' size='20' name='".$model_name."[".$column_name."]' value='' />",
                'show_order' => 1,
                'created_at' => time (),
                'updated_at' => time ()
            );
            $inputService->add ( $input );
        }

        // 生成菜单项
        if (isset($model['is_inner']) && 0 == $model['is_inner']) {
            $this->addMenuItem($model);
        }

        // 生成节点
        $ctrlName = $this->getCtrlName($model['tbl_name']);
        $nodeService = D('Node', 'Service');
        $amns = $nodeService->addModuleNodes($model['menu_name'], $ctrlName);

        if (false === $addStatus
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
        if (strpos($tblName, '.') === false) {
            // 去掉表前缀
            $tblName = substr($tblName, strpos($tblName, '_') + 1);
            $tblName = str_replace('_', ' ', $tblName);
            // 单词首字母转为大写
            $tblName = ucwords($tblName);

            $tblName = str_replace(' ', '', $tblName);
        }

        return $tblName;
    }

    /**
     * 以控制器名得到表名称
     * @param  string $ctrlName 控制器名
     * @return string
     */
    public function getTblName($ctrlName) {
        $tblName = '';
        $ctrArr = explode('.', $ctrlName);

        if (strpos($ctrlName, '.') === false || (strpos($ctrlName, '.') &&  $ctrArr[0] == C('DB_NAME') && $ctrlName = $ctrArr[1])) {
            for ($i = 0, $len = strlen($ctrlName); $i < $len; $i++) {
                if (strtoupper($ctrlName[$i]) === $ctrlName[$i]) {
                    // 大写字母
                    $tblName .= '_' . strtolower($ctrlName[$i]);
                    continue ;
                }

                $tblName .= $ctrlName[$i];
            }

            $tblName = C('DB_PREFIX') . substr($tblName, 1);
        } else {
            $tblName = $ctrlName;
        }

        return $tblName;
    }

    protected function getModelName() {
        return 'Model';
    }

    public function diff_table($model_id, $tbl_name, $sync = false) {
        $m = $this->getM();
        $db_name = C("DB_NAME");
        $tmp_name = $tbl_name;

        if(strpos($tbl_name, ".") !== false) {
            $tmp = explode(".", $tbl_name);
            $db_name = $tmp[0];
            $tbl_name = $tmp[1];
        }

        $model_field = $m->query("select name,type,length,value,comment,id from ea_field where model_id= $model_id");
        $table_field = $m->query("SELECT `COLUMN_NAME`,DATA_TYPE,COLUMN_TYPE,COLUMN_COMMENT FROM information_schema.columns WHERE table_schema ='".$db_name."' AND table_name = '".$tbl_name."'" );

        $result = [];
        $field_names = [];
        $diff_result = [];
        if($table_field) {
            foreach ( $table_field as $tk => $tv) {
                $length = "";
                if(preg_match('/\d+/',$tv['COLUMN_TYPE'], $arr)){
                    $length = $arr[0];
                }
                $tmp = [
                    'name'=>$tv['COLUMN_NAME'],
                    'type'=>$tv['DATA_TYPE'],
                    'length'=>$length,
                    'comment'=>$tv['COLUMN_COMMENT']
                ];

                $field_names[] = $tmp['name'];
                $result[$tmp['name']] = $tmp;
            }
        }

        if($model_field) {
            foreach ( $model_field as $mk => $mv ) {
                if( !in_array($mv['name'], $field_names)) {

                } else {
                    $tmp = $result[$mv['name']];
                    $tmp['id'] = $mv['id'];
                    unset($result[$mv['name']]);
                    foreach($tmp as $k=>$v) {
                        if(strtolower($mv[$k]) != strtolower($v)) {
                            unset($mv['value']);
                            $diff_result['diff'][$mv['name']] = [$tmp, $mv];
                            break;
                        }
                    }
                }
            }
        }

        if($result) {
            $diff_result['new'] = $result;
        }

        if($sync) {
            foreach($diff_result['new'] as $dv) {
                $r = $this->update_field(0, $model_id, $tmp_name, $dv['name'], $dv['comment'], $dv['type'], $dv['length']);
            }

            foreach($diff_result['diff'] as $dv) {
                $dv = $dv[0];
                $r = $this->update_field($dv['id'], $model_id, $tmp_name, $dv['name'], $dv['comment'], $dv['type'], $dv['length']);
            }
            return true;
        }

        return $diff_result;
    }

    protected function update_field($field_id, $model_id, $tablename, $name, $comment, $type, $len) {
        //获取目标表字段到本库
        $fieldService = D ( 'Field', 'Service' );
        $inputService = D('input', 'Service');

        $field = array (
            'model_id' => $model_id,
            'name' => $name,
            'comment' => $comment,
            'type' => $type,
            'length'=> $len,
            'is_requier' => 0,
            'is_unique' => 0,
            'is_index' => 0,
            'is_system' => 0,
            'created_at' => time(),
            'updated_at' => time(),
            'is_old' => 1
        );

        $is_update = false;

        if($field_id) {
            $is_update = true;
            $field['id'] = $field_id;
            $retField = $fieldService->update($field);
        }else {
            $retField = $fieldService->add($field);
            $field_id = $retField['data'] ['id'];
        }

        //插入input
        $input = array (
            'field_id' => $field_id,
            'is_show' => 1,
            'label' => $comment,
            'remark' => $comment,
            'type' => 'text',
            'width' => 20,
            'height' => 0,
            'opt_value' => '',
            'value' => '',
            'editor' => 'all',
            'html' => "<input type='text' class='input' size='20' name=".$tablename."[{$name}]' value='' />",
            'show_order' => 1,
            'created_at' => time (),
            'updated_at' => time ()
        );

        if($is_update) {
            $id = M('Input')->where(['field_id' => $field_id])->getField('id');
            $input['id'] = $id;
            $retInput = $inputService->update($input);
        } else {
            $retInput = $inputService->add($input);
        }

        if($retField['status'] && $retInput['status']) {
            return true;
        } else {
            return false;
        }
    }
}
