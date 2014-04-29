<?php
namespace Home\Controller;

/**
 * EmptyController
 * 空控制器
 */
class EmptyController extends CommonController {
    /**
     * 执行过滤
     * @return
     */
    public function _initialize() {
        parent::_initialize();
        $this->ensureExistContoller();
    }

    /**
     * 模型数据列表首页
     * @return
     */
    public function index() {
        // 得到数据表名称
        $tblName = C('DB_PREFIX') . strtolower(CONTROLLER_NAME);
        $model = M('Model')->getByTblName($tblName);
        if (!$model) {
            return $this->error('系统出现错误了！');
        }

        // 得到数据表中的所有数据
        $result = M(CONTROLLER_NAME)->select();
        // 得到模型对应的非系统字段
        $where = array(
            'model_id' => $model['id'],
            'is_system' => 0,
            'is_list_show' => 1
        );
        $fields = D('Field')->where($where)->select();

        $this->assign('model', $model);
        $this->assign('fields', $fields);
        $this->assign('result', $result);
        $this->assign('rows', count($result));
        $this->display('Default/index');
    }

    /**
     * 添加模型数据
     * @return
     */
    public function add() {
        $model = M('Model')->getByTblName($this->getTblName(CONTROLLER_NAME));

        // 得到模型对应的非系统字段
        $where = array(
            'model_id' => $model['id'],
            'is_system' => 0,
        );
        $fields = M('Field')->where($where)->select();

        // 得到字段对应的表单域
        $inputs = array();
        $orders = array();
        foreach ($fields as $key => $field) {
            $input = M('Input')->getByFieldId($field['id']);
            if ($input['is_show']) {
                $inputs[$key] = $input;
                $orders[$key] = $inputs[$key]['show_order'];
            }
        }
        // 排序表单域
        array_multisort($orders, $inputs);

        $this->assign('model', $model);
        $this->assign('inputs', $inputs);
        $this->display('Default/add');
    }

    /**
     * 创建模型数据
     * @return
     */
    public function create() {
        $data = array_map(trim, $_POST[strtolower(CONTROLLER_NAME)]);

        // 得先得到这个模型的所有字段
        $model = M('Model')->getByTblName($this->getTblName(CONTROLLER_NAME));
        $fields = D('Field')->relation(true)
                            ->where("model_id={$model['id']}")
                            ->select();

        $once = false;
        $uploadInfo = null;
        $uploadDir = C('UPLOAD_ROOT') . CONTROLLER_NAME . '/';

        foreach ($fields as $key => $field) {
            if ('file' == $field['input']['type']) {
                if (!$once) {
                    // 只执行一次上传
                    $uploadInfo = upload($uploadDir);
                    if (false === $uploadInfo['status']
                        && !empty($uploadInfo['info'])) {
                        return $this->errorReturn($uploadInfo['info']);
                    }

                    $once = true;
                }

                if (true === $uploadInfo['status']
                    && !$this->isEmpty($_FILES[$field['name']]['tmp_name'])
                    && is_array($uploadInfo['info'][0])) {

                    if (convMb2B($field['input']['width']) < $uploadInfo['info'][0]['size']) {
                        // 删除已上传的文件
                        foreach ($uploadInfo['info'] as $upload) {
                            // 删除文件
                            unlink($upload['path']);
                        }

                        // 超过限制大小
                        return $this->errorReturn("{$field['name']}不能超过{$field['input']['width']}M！");
                    }

                    $data[$field['name']] = $uploadInfo['info'][0]['path'];
                    array_shift($uploadInfo['info']);
                }
            }

            // 字段必填
            if (1 != $field['is_system']
                && 1 == $field['is_require']
                && empty($field['auto_fill'])
                && (!isset($data[$field['name']])
                    || empty($data[$field['name']]))) {
                return $this->errorReturn("{$field['comment']}必需填写！");
            }

            // 字段唯一
            if (1 != $field['is_system']
                && 1 == $field['is_unique']
                && !empty($data[$field['name']])
                && !$this->isRowUnique(CONTROLLER_NAME,
                                       $field['name'],
                                       $data[$field['name']])) {
                return $this->errorReturn("{$field['comment']}已经存在！");
            }

            // 系统字段 auto_fill 自动填充
            if ($field['is_system'] && !empty($field['auto_fill'])) {
                $data[$field['name']] = $field['auto_fill']();
            }

            // 自定义字段 auto_filter 自动过滤
            if (!empty($field['auto_filter'])) {
                if (!function_exists($field['auto_filter'])) {
                    return $this->errorReturn("过滤函数 {$field['auto_filter']} 不存在，请先进行注册函数！");
                }

                $data[$field['name']] = $field['auto_filter']($data[$field['name']]);
            }

            // 自定义字段 auto_fill 自动填充
            if (!empty($field['auto_fill'])) {
                if (!function_exists($field['auto_fill'])) {
                    return $this->errorReturn("填充函数 {$field['auto_fill']} 不存在，请先进行注册函数！");
                }

                $data[$field['name']] = $field['auto_fill']($data[$field['name']]);
            }
        }

        // 插入数据
        if (false === M(CONTROLLER_NAME)->add($data)) {
            return $this->errorReturn('添加数据失败！');
        }

        return $this->successReturn('成功添加数据！',
                                    U(CONTROLLER_NAME . '/index'));
    }

    /**
     * 编辑模型数据
     * @return
     */
    public function edit() {
        $this->display('Default/edit');
    }

    /**
     * 更新模型数据
     * @return
     */
    public function update() {
         var_dump('update');
    }

    /**
     * 空操作
     * @return
     */
    public function _empty() {
        return $this->error('亲，您访问的页面不存在！');
    }

    /**
     * 确保控制器对应的菜单存在
     * @return
     */
    protected function ensureExistContoller() {
    	$menu = fast_cache('model_menu', '', APP_PATH . '/Common/Conf/');
        if (!array_key_exists(CONTROLLER_NAME, $menu)) {
            return $this->_empty();
        }
    }

    /**
     * 得到数据表名称
     * @param  string $ctrlName
     * @return string
     */
    private function getTblName($ctrlName) {
        return C('DB_PREFIX') . strtolower($ctrlName);
    }

    /**
     * 检查字段值唯一
     * @param  string  $mn  模型名称
     * @param  string  $fn  字段名称
     * @param  string  $val 字段值
     * @return boolean
     */
    private function isRowUnique($mn, $fn, $val) {
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
}
