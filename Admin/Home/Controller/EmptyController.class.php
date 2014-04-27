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
        var_dump('craete');
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
}
