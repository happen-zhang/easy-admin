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
                    if (convMb2B($field['input']['width']) < $uploadInfo['info'][0]['size']) {
                        // 删除已上传的文件
                        foreach ($uploadInfo['info'] as $upload) {
                            // 删除文件
                            unlink($upload['path']);
                        }

                        // 超过限制大小
                        $msg ="{$fn}文件大小不能超过{$field['input']['width']}M！";
                        return $this->errorResultReturn($msg);
                    }

                    $data[$fn] = $uploadInfo['info'][0]['path'];
                    array_shift($uploadInfo['info']);
                }
            }

            // 字段必填
            if (1 != $field['is_system']
                && 1 == $field['is_require']
                && empty($field['auto_fill'])
                && (!isset($data[$fn]) || empty($data[$fn]))) {
                return $this->errorResultReturn("{$field['comment']}必需填写！");
            }

            // 字段唯一
            if (1 != $field['is_system']
                && 1 == $field['is_unique']
                && !empty($data[$fn])
                && !$this->isRowUnique($ctrlName, $fn, $data[$fn])) {
                return $this->errorResultReturn("{$field['comment']}已经存在！");
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
            if (!empty($field['auto_fill'])) {
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
