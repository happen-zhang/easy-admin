<?php
namespace Home\Service;

/**
 * CommonService
 */
abstract class CommonService {
    /**
     * 得到数据行数
     * @param  array $where
     * @return int
     */
    public function getCount(array $where) {
        $ret = $this->getM()->where($where)->count();
        return $ret;
    }

    /**
     * 得到分页数据
     * @param  array $where    分页条件
     * @param  int   $firstRow 起始行
     * @param  int   $listRows 行数
     * @return array
     */
    public function getPagination($where, $fields,$order,$firstRow,$listRows) {
        // 是否关联模型
        $M = $this->isRelation() ? $this->getD()->relation(true) : $this->getM();

        // 需要查找的字段
        if (isset($fields)) {
            $M = $M->field($fields);
        }

        // 条件查找
        if (isset($where)) {
            $M = $M->where($where);
        }

        // 数据排序
        if (isset($order)) {
            $M = $M->order($order);
        }

        // 查询限制
        if (isset($firstRow) && isset($listRows)) {
            $M = $M->limit($firstRow . ',' . $listRows);
        } else if (isset($listRows) && isset($firstRow)) {
            $M = $M->limit($listRows);
        }

        return $M->select();
    }

    /**
     * 返回结果值
     * @param  int   $status
     * @param  fixed $data
     * @return array
     */
    protected function resultReturn($status, $data) {
        return array('status' => $status,
                     'data' => $data);
    }

    /**
     * 返回错误的结果值
     * @param  string $error 错误信息
     * @return array         带'error'键值的数组
     */
    protected function errorResultReturn($error) {
        return $this->resultReturn(false, array('error' => $error));
    }

    /**
     * 得到M
     * @return Model
     */
    protected function getM() {
        $model_name = $this->getModelName();
        if (strpos($model_name, '.') !== false) {
            return M($model_name, null);
        } else {
            return M($model_name);
        }
    }

    /**
     * 得到D
     * @return Model
     */
    protected function getD() {
        return D($this->getModelName());
    }

    /**
     * 是否关联查询
     * @return boolean
     */
    protected function isRelation() {
        return true;
    }

    /**
     * 得到模型的名称
     * @return string
     */
    protected abstract function getModelName();

    protected function getCtrName() {
        $ctrName = CONTROLLER_NAME;

        if(strpos($ctrName, '.') !== false && strtoupper($ctrName[0]) === $ctrName[0]) {
            $ctrName[0] = strtolower($ctrName[0]);
        }

        return $ctrName;
    }
}
