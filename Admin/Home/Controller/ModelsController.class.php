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
        $modelService = D('Model', 'Service');
        $models = $modelService->getModels();
        $models_count = $modelService->getCount();

        $this->assign('models', $models);
        $this->assign('models_count', $models_count);
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
        $this->display();
    }
}
