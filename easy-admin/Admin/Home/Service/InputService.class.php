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
            $field['model'] = $this->getInputModelName($field['model_id']);
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
        $field['model'] = $this->getInputModelName($field['model_id']);
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

    /**
     * 是否file表单域
     * @param  string  $type 类型
     * @return boolean
     */
    public function isFileInput($type) {
        if ('file' == $type) {
            return true;
        }

        return false;
    }

    /**
     * 是否checkbox
     * @param  string  $type 类型
     * @return boolean
     */
    public function isCheckbox($type) {
        if ('checkbox' == $type) {
            return true;
        }

        return false;
    }

    /**
     * 得到添加表单域
     * @param  string $tblName 模型数据表
     * @return array
     */
    public function getAddInputsByTblName($tblName) {
        $model = M('Model')->getByTblName($tblName);

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

        return $inputs;
    }

    /**
     * 得到带值编辑的表单域
     * @param  string $tblName 模型数据表
     * @param  array  $data    表单域值
     * @return array
     */
    public function getEditInputsByTblName($tblName, $data) {
        $types = array('checkbox', 'select', 'radio', 'relation_select');

        $inputs = $this->getAddInputsByTblName($tblName);
        $model = M('Model')->getByTblName($tblName);

        // 生成带有默认值的表单域
        foreach ($inputs as $key => $input) {
            $field = M('Field')->getById($input['field_id']);
            $field['model'] = $this->getInputModelName($model['id']);

            if (in_array($input['type'], $types)) {
                // 处理opt_value中的默认项
                $i = 0;
                $inputLogic = D('Input', 'Logic');

                $opts = $inputLogic->optValueToArray($input['opt_value']);
                foreach ($opts['opt_value'] as $opt) {
                    if ($opt == $data[$field['name']]) {
                        $opts['selected'] = $i;
                        break ;
                    }
                    $i += 1;
                }

                if ('checkbox' == $input['type']) {
                    $selected = explode(',', $data[$field['name']]);
                    $opts['selected'] = '';
                    foreach ($selected as $item) {
                        $pos = array_pos($opts['opt_value'], $item);
                        if (false !== $pos) {
                            $opts['selected'] .= "{$pos},";
                        }
                    }

                    $opts = $inputLogic->optArrayToString($opts, true);
                } else {
                    $opts = $inputLogic->optArrayToString($opts);
                }

                // 已得到realtion_select的可选项
                if ('relation_select' == $input['type']) {
                    $inputs[$key]['type'] = 'select';
                }

                $inputs[$key]['opt_value'] = $opts;
            } else {
                $value = strip_sql_injection($data[$field['name']]);
                $inputs[$key]['value'] = $value;
            }

            // 生成带值的表单域
            D('Input', 'Logic')->genHtml($inputs[$key], $field);
        }

        return $inputs;
    }

    /**
     * 更新关联表单域
     * @param  array  $data    更新的数据
     * @param  int    $modelId 模型id
     * @return
     */
    public function updateRalationInput(array $data, $modelId) {
        $fieldService = D('Field', 'Service');
        $inputLogic = D('Input', 'Logic');

        $fields = $fieldService->getByModelId($modelId);
        $updKeys = array_keys($data);
        $updFields = array();
        foreach ($fields as $field) {
            if (!in_array($field['name'], $updKeys)) {
                continue ;
            }

            $rFields=$fieldService->getRelatedFields($modelId, $field['name']);
            foreach ($rFields as $rField) {
                $updFields[] = $rField;
            }
        }

        foreach ($updFields as $updField) {
            $opts = $inputLogic->getRelationOpts($updField);
            $fn = $this->getInputModelName($updField['model_id'])
                         . "[$updField[name]]";

            $udpInput = array(
                'opt_value' => $inputLogic->optArrayToString($opts),
                'html' => genSelect($fn, $opts['opt_value'])
            );

            // update
            $this->getM()
                 ->where("id={$updField['input']['id']}")
                 ->save($udpInput);
        }

        return ;
    }

    /**
     * 得到模型小写作为表单域的name
     * @param  int    $modelId 模型的id
     * @return string
     */
    protected function getInputModelName($modelId) {
        $model = M('Model')->field('tbl_name')->getById($modelId);
        $ctrlName = D('Model', 'Service')->getCtrlName($model['tbl_name']);

        return strtolower($ctrlName);
    }

    protected function getModelName() {
        return 'Input';
    }
}
