<?php
namespace Home\Controller;

/**
 * ModelsController
 * 模型管理
 */
class ModelsController extends CommonController {
    /**
     * 模型列表
     * @return
     */
    public function index(){
        $result = $this->getPagination('Model');

        $this->assign('models', $result['data']);
        $this->assign('models_count', $result['total_rows']);
        $this->assign('page', $result['show']);
        $this->display();
    }

    /**
     * 模型信息
     * @return
     */
    public function show() {
        $this->display();
    }

    /**
     * 添加模型
     * @return
     */
    public function add() {
        $this->display();
    }

    /**
     * 创建模型
     * @return
     */
    public function create() {
        if (!IS_POST || !isset($_POST['model'])) {
            return $this->errorReturn('无效的操作');
        }

        $modelService = D('Model', 'Service');
        $model = array_map('trim', $_POST['model']);

        // 检查数据是否合法
        $result = $modelService->checkModel($model);
        if (false === $result['status']) {
            return $this->errorReturn($result['data']['error']);
        }

        // 添加数据
        $result = $modelService->add($model);
        if (false === $result['status']) {
            return $this->errorReturn('系统出错了');
        }

        $this->successReturn("添加模型[{$model['name']}]成功", U('Models/index'));
    }

    /**
     * 检查模型名可用性
     * @return
     */
    public function checkModelName() {
        if (isset($_GET['id'])) {
            $_SESSION['update_id'] = $_GET['id'];
        }

        $result = D('Model', 'Service')->checkModelName($_GET['model_name']);
        if ($result['status']) {
            return $this->successReturn('模型名称可用');
        }

        return $this->errorReturn($result['data']['error']);
    }

    /**
     * 检查数据表名可用性
     * @return
     */
    public function checkTblName($tableName = null) {
        if (isset($_GET['id'])) {
            $_SESSION['update_id'] = $_GET['id'];
        }

        $tblName = isset($tableName) ? $tableName : $_GET['tbl_name'];
        $result = D('Model', 'Service')->checkTblName($tblName);

        if ($result['status']) {
            return $this->successReturn('数据表名称可用');
        }

        return $this->errorReturn($result['data']['error']);
    }

    /**
     * 编辑模型
     * @return
     */
    public function edit() {
        if (!isset($_GET['id'])) {
            $this->error('您需要编辑的模型不存在');
        }

        $model = M('Model')->getById($_GET['id']);
        if (empty($model)) {
            $this->error('您需要编辑的模型不存在');
        }

        // 检查唯一性的id
        $_SESSION['update_id'] = $_GET['id'];

        $start = strpos($model['tbl_name'], '_') + 1;
        $model['tbl_name'] = substr($model['tbl_name'], $start);

        $this->assign('model', $model);
        $this->display();
    }

    /**
     * 更新模型
     * @return
     */
    public function update() {
        if (!IS_POST || !isset($_POST['model'])) {
            $this->errorReturn('无效的操作');
        }

        $model = array_map('trim', $_POST['model']);
        if (0 == M('Model')->where(array('id' => $model['id']))->count()) {
            $this->errorReturn('您需要更新的模型不存在');
        }

        $modelService = D('Model', 'Service');
        // 检查数据是否合法
        $_SESSION['update_id'] = $model['id'];
        $result = $modelService->checkModel($model);
        if (false === $result['status']) {
            return $this->errorReturn($result['data']['error']);
        }

        // 更新数据
        $result = $modelService->update($model);
        if (false === $result['status']) {
            return $this->errorReturn('系统出错了');
        }

        $this->successReturn("更新模型成功", U('Models/index'));
    }
}
