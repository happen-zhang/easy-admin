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
     * 编辑模型
     * @return
     */
    public function edit() {
        $this->display();
    }
}
