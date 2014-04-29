<?php
namespace Home\Service;

/**
 * DefaultService
 */
class DefaultService extends CommonService {
    /**
     * 创建数据
     * @param  array $data   提交的数据
     * @param  array $fields 对应模型的数据
     * @return array
     */
    public function create(array $data, array $fields, $ctrlName) {
        $once = false;
        $uploadInfo = null;
        $uploadDir = C('UPLOAD_ROOT') . $ctrlName . '/';

        foreach ($fields as $field) {
            $fn = $field['name'];
            $fm = $field['comment'];

            // 是否文件类型的表单域
            if (D('Input', 'Service')->isFileInput($field['input']['type'])) {
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
                            unlink($upload['path']);
                        }

                        // 超过限制大小
                        $msg ="{$fm}文件大小不能超过{$field['input']['width']}M！";
                        return $this->errorResultReturn($msg);
                    }

                    $data[$fn] = $uploadInfo['info'][0]['path'];
                    array_shift($uploadInfo['info']);
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
                && !empty($data[$fn])
                && !$this->isRowUnique($ctrlName, $fn, $data[$fn])) {
                return $this->errorResultReturn("{$fm}已经存在！");
            }

            // 系统字段 auto_fill 自动填充
            if ($field['is_system'] && !empty($field['auto_fill'])) {
                $data[$fn] = $field['auto_fill']();
            }

            // 自定义字段 auto_filter 自动过滤
            if (!empty($field['auto_filter'])) {
                if (!function_exists($field['auto_filter'])) {
                    $msg ="过滤函数{$field['auto_filter']}不存在，请先进行注册函数！";
                    return $this->errorResultReturn($msg);
                }

                $data[$fn] = $field['auto_filter']($data[$fn]);
            }

            // 自定义字段 auto_fill 自动填充
            if (!empty($field['auto_fill']) && empty($data[$fn])) {
                if (!function_exists($field['auto_fill'])) {
                    $msg = "填充函数{$field['auto_fill']}不存在，请先进行注册函数！";
                    return $this->errorResultReturn($msg);
                }

                $data[$fn] = $field['auto_fill']($data[$fn]);
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
    public function isRowUnique($mn, $fn, $val) {
        $where = array($fn => $val);

        if (M($mn)->where($where)->count() > 0) {
            return false;
        }

        return true;
    }

    /**
     * 判断是否为空
     * @param  mixed  $mixed 需要检查的值
     * @return boolean
     */
    private function isEmpty($mixed) {
        if (is_array($mixed)) {
            return empty(array_filter($mixed));
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

    protected function getModelName() {
        return '';
    }
}
