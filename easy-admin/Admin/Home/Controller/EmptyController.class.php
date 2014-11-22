<?php
namespace Home\Controller;

/**
 * EmptyController
 * 空控制器
 */
class EmptyController extends CommonController {
    /**
     * 需要拆分列表值的表单域类型
     * @var array
     */
    public $types = array('checkbox', 'select', 'radio');

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
        $tblName = D('Model', 'Service')->getTblName(CONTROLLER_NAME);
        $model = M('Model')->getByTblName($tblName);
        if (!$model) {
            return $this->error('系统出现错误了！');
        }

        // 得到分页数据
        $result = $this->getPagination('Default', null, null, 'id DESC');
        $rows = array_map("strip_sql_injection", $result['data']);
        unset($result['data']);

        // 得到模型对应的字段
        $where = array(
            'model_id' => $model['id'],
            'is_list_show' => 1
        );
        $fields = D('Field')->relation(true)->where($where)->select();

        // 处理需要替换的字段值
        foreach ($fields as $field) {
            $fn = $field['name'];

            // created_at、updated_at换成日期格式
            if (($field['is_system'] && $field['is_list_show'])
                && ('created_at' == $fn || 'updated_at' == $fn)) {
                foreach ($rows as $key => $row) {
                    $rows[$key][$fn] = date('Y-m-d H:i:s', $row[$fn]);
                }
            }

            // checkbox，radio，select类型
            if (in_array($field['input']['type'], $this->types)
                && !empty($field['input']['opt_value'])) {
                $opts = D('Input', 'Logic')
                         ->optValueToArray($field['input']['opt_value']);
                $opts = array_flip($opts['opt_value']);

                foreach ($rows as $key => $row) {
                    if ('checkbox' == $field['input']['type']) {
                        $value = '';
                        $values = explode(',', $row[$fn]);
                        foreach ($values as $val) {
                            $value .= "{$opts[$val]}<br/>";
                        }
                        $rows[$key][$fn] = $value;
                    } else {
                        $rows[$key][$fn] = $opts[$row[$fn]];
                    }
                }
            }

            // 关联表类型
            if (0 != $field['relation_model']
                && !empty($field['relation_value'])
                && !empty($field['relation_field'])) {
                // 被关联的模型
                $rModel = M('Model')->getById($field['relation_model']);
                // 表模型名
                $mn = D('Model', 'Service')->getCtrlName($rModel['tbl_name']);

                foreach ($rows as $key => $row) {
                    $tmp = "{$field['relation_field']}={$row[$fn]}";
                    $rField = M($mn)->where($tmp)
                                    ->field("{$field['relation_value']}")
                                    ->find();
                    $rows[$key][$fn] = $rField[$field['relation_value']];
                }
            }
        }

        $this->assign('model', $model);
        $this->assign('fields', $fields);
        $this->assign('rows', $rows);
        $this->assign('rows_count', $result['total_rows']);
        $this->assign('page', $result['show']);
        $this->display('Default/index');
    }

    /**
     * 添加模型数据
     * @return
     */
    public function add() {
        $tblName = D('Model', 'Service')->getTblName(CONTROLLER_NAME);
        $inputs = D('Input', 'Service')->getAddInputsByTblName($tblName);

        $this->assign('inputs', $inputs);
        $this->display('Default/add');
    }

    /**
     * 创建模型数据
     * @return
     */
    public function create() {
        // 得先得到这个模型的所有字段
        $fields = D('Field', 'Service')->getByCtrlName(CONTROLLER_NAME);
        $defaultService = D('Default', 'Service');

        // 创建数据
        $data = $_POST[strtolower(CONTROLLER_NAME)];
        $result = $defaultService->create($data, $fields, CONTROLLER_NAME);
        if (!$result['status']) {
            return $this->errorReturn($result['data']['error']);
        }

        // 插入数据
        $result = $defaultService->add($result['data'], CONTROLLER_NAME);
        if (!$result['status']) {
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
        $data = M(CONTROLLER_NAME)->where("id={$_GET['id']}")->find();

        if (is_null($data)) {
            return $this->error('需要编辑的数据不存在！');
        }

        $tblNmae = D('Model', 'Service')->getTblName(CONTROLLER_NAME);
        $inputs = D('Input','Service')->getEditInputsByTblName($tblNmae,$data);
        $hidden = array(
            'name' => strtolower(CONTROLLER_NAME) . '[id]',
            'value' => $_GET['id']
        );

        $this->assign('hidden', $hidden);
        $this->assign('inputs', $inputs);
        $this->display('Default/edit');
    }

    /**
     * 更新模型数据
     * @return
     */
    public function update() {
        $iname = strtolower(CONTROLLER_NAME);
        if (!isset($_POST[$iname]['id'])
            || is_null(M(CONTROLLER_NAME)->getById($_POST[$iname]['id']))) {
            return $this->errorReturn('无效的操作！');
        }

        $defaultService = D('Default', 'Service');
        $fields = D('Field', 'Service')->getByCtrlName(CONTROLLER_NAME);

        // 创建数据
        $data = $_POST[$iname];
        $result = $defaultService->create($data,
                                          $fields,
                                          CONTROLLER_NAME,
                                          'update');
        if (!$result['status']) {
            return $this->errorReturn($result['data']['error']);
        }

        // 更新数据
        $result = $defaultService->update($result['data'], CONTROLLER_NAME);
        if (!$result['status']) {
            return $this->errorReturn('更新数据失败！');
        }

        return $this->successReturn('更新数据成功！',
                                    U(CONTROLLER_NAME . '/index'));
    }

    /**
     * 删除模型数据
     * @return
     */
    public function delete() {
        if (!isset($_GET['id'])
            || is_null(M(CONTROLLER_NAME)->getById($_GET['id']))) {
            return $this->errorReturn('需要删除的数据不存在！');
        }

        $result = D('Default', 'Service')->delete($_GET['id'],CONTROLLER_NAME);
        if (!$result['status']) {
            return $this->errorReturn('删除数据失败！');
        }

        return $this->successReturn('成功删除数据！');
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
}
