<?php
namespace Home\Service;

/**
 * InputService
 */
class InputService extends CommonService {
    /**
     * 添加input
     * @param array input
     * @return
     */
    public function add($input) {
        $Input = $this->getD();
        $input = $Input->create($input);
        if (false === $Input->add($input)) {
            return $this->resultReturn(false);
        }

        return $this->resultReturn(true);
    }

    /**
     * 更新input
     * @param  array $input
     * @return array
     */
    public function update($input) {
        if (!D('Field', 'Service')->existField($input['field_id'])) {
            return $this->resultReturn(false);
        }

        $Input = $this->getD();
        if (!$this->existInput($input['id'])) {
            return $this->resultReturn(false);
        }

        // 更新html
        $old = $Input->getById($input['id']);
        $field = M('Field')->getById($input['field_id']);
        if ($input['type'] != $old['type']
            || $input['width'] != $old['width']
            || $input['height'] != $old['height']
            || $input['value'] != $old['value']
            || $input['opt_value'] != $old['opt_value']
            || $input['editor'] != $old['editor']
            || false === strpos($input['html'], $field['name'])) {
            D('Input', 'Logic')->genHtml($input, $field);
        }

        $input = $Input->create($input);
        if (false === $Input->save($input)) {
            return $this->resultReturn(false);
        }

        return $this->resultReturn(true);
    }

    /**
     * 检查表单域是否可用
     * @param  array $input Input数组
     * @param  int   $id    需要更新input的id
     * @return mixed
     */
    public function checkInput($input, $id) {
        $Input = $this->getD();
        if ($Input->isValid($input, $id)) {
            return $this->resultReturn(true);
        }

        return $this->errorResultReturn($Input->getError());
    }

    /**
     * 创建input
     * @param  array $input
     * @param  array $field
     * @return array
     */
    public function create(&$input, $field) {
        $inputLogic = D('Input', 'Logic');

        // 处理表单域长度
        $inputLogic->genSize($input);

        // 生成表单域html
        if (!isset($input['html']) || '' == $input['html']) {
            $inputLogic->genHtml($input, $field);
        }

        return $this->getD()->create($input);
    }

    /**
     * input是否存在
     * @param  int $id
     * @return boolean
     */
    public function existInput($id) {
        if ($this->getM()->where("id = {$id}")->count() > 0) {
            return true;
        }

        return false;
    }

    protected function getModelName() {
        return 'Input';
    }
}
