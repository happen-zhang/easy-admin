<?php
namespace Home\Controller;

/**
 * FieldsController
 * 字段管理
 */
class FieldsController extends CommonController {
    /**
     * 字段列表
     * @return
     */
    public function index(){
        $this->display();
    }

    /**
     * 字段信息
     * @return
     */
    public function show() {
        $this->display();
    }    

    /**
     * 添加字段
     * @return
     */
    public function add() {
        if (!isset($_GET['model_id'])) {
            return $this->error('您需要添加字段的模型不存在');
        }

        // 得到可选的关联模型
        $models = D('Model', 'Service')->getAll();

        $model = M('Model')->getById($_GET['model_id']);
        if (empty($model)) {
            return $this->error('您需要添加字段的模型不存在');
        }

        $this->assign('models', $models);
        $this->assign('model', $model);
        $this->display();
    }

    /**
     * 检查字段名称可用性
     * @return
     */
    public function checkFieldName() {
        $result = D('Field', 'Service')->checkFieldName($_GET['field_name'],
                                                        $_GET['model_id']);
        if ($result['status']) {
            return $this->successReturn('字段名称可用');
        }

        return $this->errorReturn($result['data']['error']);
    }

    /**
     * 检查字段标签可用性
     * @return
     */
    public function checkFieldLabel() {
        $result = D('Field', 'Service')
                   ->checkFieldComment($_GET['field_label'],
                                       $_GET['model_id']);
        if ($result['status']) {
            return $this->successReturn('字段标签可用');
        }

        return $this->errorReturn($result['data']['error']);    
    }

    /**
     * 编辑字段
     * @return
     */
    public function edit() {
        $this->display();
    }
}
