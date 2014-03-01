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
