<?php
namespace Home\Controller;

/**
 * ModelsController
 * 模型管理
 */
class ModelsController extends CommonController
{
    /**
     * 模型列表
     */
    public function index()
    {
        $result = $this->getPagination('Model');

        $this->assign('models', $result['data']);
        $this->assign('models_count', $result['total_rows']);
        $this->assign('page', $result['show']);
        $this->display();
    }

    /**
     * 模型信息
     * @return mixed|void
     */
    public function show()
    {
        if (!isset($_GET['id'])) {
            $this->error('您需要查看的模型不存在！');
        }

        $model = D('Model', 'Service')->getById($_GET['id']);
        if (empty($model)) {
            $this->error('您需要查看的模型不存在！');
        }

        // 得到input的显示顺序
        $orders = array();
        foreach ($model['fields'] as $key => $field) {
            $input = M('Input')->field('show_order')
                ->where("field_id={$field['id']}")
                ->find();
            $model['fields'][$key]['show_order'] =
                (is_null($input) || 0 == $input['show_order'])
                    ? 0 : $input['show_order'];
            $orders[$key] = $model['fields'][$key]['show_order'];
        }

        // field按show_order排序
        array_multisort($orders, $model['fields']);

        $this->assign('model', $model);
        $this->display();
    }

    /**
     * 添加模型
     */
    public function add()
    {

        $db_data = [];

        $m = M();
        $databases = $m->query('select SCHEMA_NAME as db_name from information_schema.SCHEMATA');
        if ($databases) {
            foreach($databases as $db_row) {
                if ($db_row["db_name"] == "information_schema")
                    continue;

                $tables = $m->query('select TABLE_NAME as tb_name from information_schema.TABLES where TABLE_SCHEMA = "'.$db_row['db_name'].'"');
                $tmp = [];
                foreach($tables as $tb_row) {
                    $tmp[] = $tb_row['tb_name'];
                }
                $db_data[$db_row['db_name']] = $tmp;
            }
        }

        $this->assign('db_data', $db_data);
        $this->display();
    }

    /**
     * 创建模型
     */
    public function create()
    {
        if (!IS_POST || !isset($_POST['model'])) {
            return $this->errorReturn('无效的操作！');
        }

        $modelService = D('Model', 'Service');
        $model = array_map('trim', $_POST['model']);
        if ($model ['radio'] == 'old' && ! empty ( $model ['tbl_name_old'] )) {
            $model ['tbl_name'] = $model['db_data'].$model ['tbl_name_old'];
        } else {
            $model['tbl_name'] = $model['db_data'].$model ['tbl_name'];
        }

        // 检查数据是否合法
        $result = $modelService->checkModel($model);
        if (false === $result['status']) {
            return $this->errorReturn($result['data']['error']);
        }

        // 添加数据
        if ($model ['radio'] == 'old' && ! empty ( $model ['tbl_name_old'] )) {
            $result = $modelService->add_old_table($model);
        } else {
            $result = $modelService->add($model);
        }

        if (false === $result['status']) {
            return $this->errorReturn('系统出错了！');
        }

        $this->successReturn("添加模型 <b>{$model['name']}</b> 成功！", U('Models/index'));
    }

    /**
     * 检查模型名可用性
     */
    public function checkModelName()
    {
        $result = D('Model', 'Service')->checkModelName($_GET['model_name'],
            $_GET['id']);
        if ($result['status']) {
            return $this->successReturn('模型名称可用！');
        }

        return $this->errorReturn($result['data']['error']);
    }

    /**
     * 检查数据表名可用性
     * @param null $tableName
     */
    public function checkTblName($tableName = null)
    {
        $tblName = isset($tableName) ? $tableName : $_GET['tbl_name'];
        $result = D('Model', 'Service')->checkTblName($tblName, $_GET['id']);

        if ($result['status']) {
            return $this->successReturn('数据表名称可用！');
        }

        return $this->errorReturn($result['data']['error']);
    }

    /**
     * 检查菜单名可用性
     */
    public function checkMenuName()
    {
        $result = D('Model', 'Service')->checkMenuName($_GET['menu_name'],
            $_GET['id']);
        if ($result['status']) {
            return $this->successReturn('菜单名称可用！');
        }

        return $this->errorReturn($result['data']['error']);
    }

    /**
     * 编辑模型
     */
    public function edit()
    {
        if (!isset($_GET['id'])) {
            $this->error('您需要编辑的模型不存在！');
        }

        $model = M('Model')->getById($_GET['id']);
        if (empty($model)) {
            $this->error('您需要编辑的模型不存在！');
        }

        $start = strpos($model['tbl_name'], '_') + 1;
        $model['tbl_name'] = substr($model['tbl_name'], $start);

        $this->assign('model', $model);
        $this->display();
    }

    /**
     * 更新模型
     */
    public function update()
    {
        if (!IS_POST || !isset($_POST['model'])) {
            $this->errorReturn('无效的操作！');
        }

        $model = array_map('trim', $_POST['model']);
        if (0 == M('Model')->where(array('id' => $model['id']))->count()) {
            $this->errorReturn('您需要更新的模型不存在！');
        }

        $modelService = D('Model', 'Service');
        $result = $modelService->checkModel($model, $model['id']);
        if (false === $result['status']) {
            return $this->errorReturn($result['data']['error']);
        }

        // 更新数据
        $result = $modelService->update($model);
        if (false === $result['status']) {
            return $this->errorReturn('系统出错了！');
        }

        $this->successReturn("更新模型成功！", U('Models/index'));
    }

    /**
     * 删除模型
     */
    public function delete()
    {
        if (!isset($_GET['id'])) {
            $this->errorReturn('您需要删除的模型不存在！');
        }

        $model = M('Model')->getById($_GET['id']);
        if (empty($model)) {
            $this->errorReturn('您需要删除的模型不存在！');
        }

        $result = D('Model', 'Service')->delete($model['id']);
        if (false === $result['status']) {
            return $this->errorReturn('系统出错了！');
        }

        $this->successReturn("删除模型 <b>{$model['name']}</b> 成功！");
    }

    /**
     * 检查表差异
     */
    public function check_table() {
        $model_id = I("post.id");
        $tbl_name = I("post.name");
        $ret = D('Model', 'Service')->diff_table($model_id, $tbl_name);

        if($ret) {
            $html = '<div class="field_diff_box">';

            foreach ($ret as $k => $v) {
                if($k == "new") {
                    $html.= '<strong>新增</strong>';
                    $html.= '<ul>';
                    foreach($v as $vo) {
                        $html.= '<li> '.$vo['name'].": ";
                        unset($vo['id']);
                        unset($vo['name']);
                        $html.= '<em>'.implode(' </em>, <em>', $vo).' </em>';
                    }
                    $html.= '</ul>';
                }

                if($k == "diff") {
                    $html.= '<strong>更新</strong>';
                    $html.= '<ul>';
                    foreach($v as $vo) {
                        $html.= '<li> '.$vo[1]['name'].": ";
                        unset($vo[1]['id']);
                        unset($vo[1]['name']);
                        unset($vo[0]['id']);
                        unset($vo[0]['name']);
                        $html.= '<em>'.implode(' </em>, <em>', $vo[0]).' => '.implode(' </em>, <em>', $vo[1]).' </em>';
                    }
                    $html.= '</ul>';
                }
            }

            $html .= "<button class=\"btn do_sync\">同步</button>";
        } else {
            $html = "<div class=\"field_diff_box\"><h3>无差异</h3></div>";
        }
        $html .= "</div>";

        $ret['html'] = $html;

        return $this->successReturn($ret);
    }

    /**
     * 差异同步至模型
     */
    public function sync_model() {
        $model_id = I("post.id");
        $tbl_name = I("post.name");
        $ret = D('Model', 'Service')->diff_table($model_id, $tbl_name, true);

        $this->successReturn("操作成功");
    }
}
