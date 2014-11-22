<?php
namespace Home\Service;

/**
 * DefaultService
 */
class DefaultService extends CommonService {
    /**
     * 填充时机
     * @var array
     */
    private $createTime = array('insert', 'update');

    /**
     * 创建数据
     * @param  array  $data     提交的数据
     * @param  array  $fields   对应模型的数据
     * @param  string $ctrlName 控制器名
     * @param  string $time     填充时机
     * @return array
     */
    public function create(array $data, array $fields, $ctrlName, $time) {
        $once = false;
        $uploadInfo = null;
        $uploadDir = C('UPLOAD_ROOT') . $ctrlName . '/';

        $data = array_map(trim_value, $data);
        $time = in_array(strtolower($time), $this->createTime) ?
                         strtolower($time) : $this->createTime[0];
        $inputService = D('Input', 'Service');

        foreach ($fields as $field) {
            $fn = $field['name'];
            $fm = $field['comment'];

            // 是否文件类型的表单域
            if ($inputService->isFileInput($field['input']['type'])) {
                if (!$once) {
                    // 只执行一次上传
                    $uploadInfo = upload($uploadDir);

                    if (false === $uploadInfo['status']
                        && !empty($uploadInfo['info'])) {
                        // 上传失败
                        return $this->errorResultReturn($uploadInfo['info']);
                    }

                    $once = true;
                }

                if (true === $uploadInfo['status']
                    && !$this->isEmpty($_FILES[$fn]['tmp_name'])
                    && is_array($uploadInfo['info'][0])) {
                    // 处理真正上传过的file表单域
                    $size = $uploadInfo['info'][0]['size'];
                    if (convMb2B($field['input']['width']) < $size) {
                        // 删除已上传的文件
                        foreach ($uploadInfo['info'] as $upload) {
                            // 删除文件
                            unlink(WEB_ROOT . $upload['path']);
                        }

                        // 超过限制大小
                        $msg ="{$fm}文件大小不能超过{$field['input']['width']}M！";
                        return $this->errorResultReturn($msg);
                    }

                    $data[$fn] = $uploadInfo['info'][0]['path'];
                    array_shift($uploadInfo['info']);
                }
            }

            // checkbox类型需要合并值
            if ($inputService->isCheckbox($field['input']['type'])) {
                if (isset($data[$fn]) && !empty($data[$fn])) {
                    $data[$fn] = $data[$fn] = implode(',', $data[$fn]);
                } else {
                    $data[$fn] = '';
                }
            }

            // 检查field[type]约束
            if (!empty($data[$fn])) {
                $result = $this->checkTypeContraint($field['type'],$data[$fn]);
                if (!$result['status'] && !empty($result['data'])) {
                    $msg = "{$fm}为{$field['type']}类型";
                    if ('int' == $result['data']) {
                        $msg .= "，值只能为整数！";
                    } else if ('double' == $reuslt['data']) {
                        $msg .= "，值只能为浮点数！";
                    }

                    return $this->errorResultReturn($msg);
                }
            }

            // 日期型格式
            if ('date' == $field['input']['type'] && !empty($data[$fn])) {
                $result = $this->checkTypeContraint($field['input']['type'],
                                                    $data[$fn]);
                if (!$result['status']) {
                    $msg = "{$fm}日期格式不正确！";
                    return $this->errorResultReturn($msg);
                }
            }

            // 字符长度
            if (('CHAR' == $field['type'] || 'VARCHAR' == $field['type'])
                && !empty($data[$fn])
                && strlen($data[$fn]) > $field['length']) {

                $msg = "{$fm}长度只能小于{$field['length']}个字符！";
                return $this->errorResultReturn($msg);
            }

            // 字段必填
            if (1 != $field['is_system']
                && 1 == $field['is_require']
                && empty($field['auto_fill'])
                && (!isset($data[$fn]) || empty($data[$fn]))) {
                return $this->errorResultReturn("{$fm}必需填写！");
            }

            // 字段唯一
            if (1 != $field['is_system']
                && 1 == $field['is_unique']
                && !empty($data[$fn])) {
                $isUnique = $this->isRowUnique($ctrlName,
                                               $field['name'],
                                               $data[$fn],
                                               $data['id']);
                if (!$isUnique) {
                    return $this->errorResultReturn("{$fm}已经存在！");
                }
            }

            // 自定义字段 auto_filter 自动过滤
            if (!empty($field['auto_filter'])) {
                if (!function_exists($field['auto_filter'])) {
                    $msg ="过滤函数{$field['auto_filter']}不存在，请先进行注册函数！";
                    return $this->errorResultReturn($msg);
                }

                $data[$fn] = $field['auto_filter']($data[$fn]);
            }

            // 系统字段 auto_fill 自动填充
            if ($field['is_system']
                && !empty($field['auto_fill'])
                && ('both' == $field['fill_time']
                    || $time == $field['fill_time'])) {

                if (!function_exists($field['auto_fill'])) {
                    $msg = "填充函数{$field['auto_fill']}不存在，请先进行注册函数！";
                    return $this->errorResultReturn($msg);
                }

                $data[$fn] = $field['auto_fill']();
            }

            // 自定义字段 auto_fill 自动填充
            if (!empty($field['auto_fill'])
                && empty($data[$fn])
                && ('both' == $field['fill_time']
                    || $time == $field['fill_time'])) {

                if (!function_exists($field['auto_fill'])) {
                    $msg = "填充函数{$field['auto_fill']}不存在，请先进行注册函数！";
                    return $this->errorResultReturn($msg);
                }

                $data[$fn] = $field['auto_fill']();
            }
        }

        return $this->resultReturn(true, $data);
    }

    /**
     * 添加数据
     * @param  array  $data     需要添加的数据
     * @param  string $ctrlName 添加数据的模型
     * @return array
     */
    public function add(array $data, $ctrlName) {
        if (false === M($ctrlName)->add($data)) {
            return $this->resultReturn(false);
        }

        $data['id'] = M($ctrlName)->getLastInsId();
        $tblName = D('Model', 'Service')->getTblName($ctrlName);
        $model = M('Model')->getByTblName($tblName);

        // 更新被关联的表单域
        $inputService = D('Input', 'Service');
        $inputService->updateRalationInput($data, $model['id']);

        return $this->resultReturn(true);
    }

    /**
     * 更新数据
     * @param  array  $data     需要更新的数据
     * @param  string $ctrlName 更新数据的模型
     * @return array
     */
    public function update(array $data, $ctrlName) {
        if (false === M($ctrlName)->save($data)) {
            return $this->resultReturn(false);
        }

        $tblName = D('Model', 'Service')->getTblName($ctrlName);
        $model = M('Model')->getByTblName($tblName);

        // 更新被关联的表单域
        $inputService = D('Input', 'Service');
        $inputService->updateRalationInput($data, $model['id']);

        return $this->resultReturn(true);
    }

    /**
     * 删除模型数据
     * @param  int    $id       删除数据的id
     * @param  string $ctrlName 删除数据的模型
     * @return array
     */
    public function delete($id, $ctrlName) {
        $old = M($ctrlName)->getById($id);

        if (is_null($old) || false === M($ctrlName)->delete($id)) {
            return $this->resultReturn(false);
        }

        $tblName = D('Model', 'Service')->getTblName($ctrlName);
        $model = M('Model')->getByTblName($tblName);

        // 级联删除
        $this->cascadeDel($model['id'], $old);

        // 更新被关联的表单域
        $inputService = D('Input', 'Service');
        $inputService->updateRalationInput($old, $model['id']);

        return $this->resultReturn(true);
    }

    /**
     * 检查类型约束
     * @param  string $type  需要约束的类型
     * @param  string $value 需要约束的值
     * @return mixed
     */
    public function checkTypeContraint($type, $value) {
        switch ($type) {
            case 'TINYINT':
            case 'SMALLINT':
            case 'INT':
            case 'BIGINT':
                if (!isint($value)) {
                    return $this->resultReturn(false, 'int');
                }
                break;

            case 'FLOAT':
            case 'DOUBLE':
                if (!isdouble($value)) {
                    return $this->resultReturn(false, 'double');
                }
                break;

            case 'date':
                if (!is_valid_date($value)) {
                    return $this->resultReturn(false, 'date');
                }
                break;
        }

        return $this->resultReturn(true);
    }

    /**
     * 检查字段值唯一
     * @param  string  $mn  模型名称
     * @param  string  $fn  字段名称
     * @param  string  $val 字段值
     * @return boolean
     */
    public function isRowUnique($mn, $fn, $val, $id = null) {
        $where = array($fn => $val);
        if (isset($id)) {
            $where['id'] = array('neq', $id);
        }

        if (M($mn)->where($where)->count() > 0) {
            return false;
        }

        return true;
    }

    /**
     * 级联删除数据
     * @param  int   $modelId 模型id
     * @param  array $data    被删除的关联数据
     * @return
     */
    public function cascadeDel($modelId, $data) {
        // 得到关联到模型的字段
        $fields = D('Field', 'Service')->getByRelationModel($modelId);
        // 级联删除关联的数据
        foreach ($fields as $field) {
            $rf = $field['relation_field'];
            $where = array($field['name'] => $data[$rf]);

            // 得到该字段所在的模型
            $model = M('Model')->getById($field['model_id']);
            M(D('Model', 'Service')->getCtrlName($model['tbl_name']))
                                   ->where($where)
                                   ->delete();
        }

        return ;
    }

    /**
     * 判断是否为空
     * @param  mixed  $mixed 需要检查的值
     * @return boolean
     */
    private function isEmpty($mixed) {
        if (is_array($mixed)) {
            $mixed = array_filter($mixed);
            return empty($mixed);
        } else {
            return empty($mixed);
        }
    }

    /**
     * 删除文件
     * @param  array $files 需要删除的文件路径
     * @return
     */
    private function unlinkFiles($files) {
        foreach ($files as $file) {
            unlink($file);
        }
    }

    protected function isRelation() {
        return false;
    }

    protected function getModelName() {
        return CONTROLLER_NAME;
    }
}
