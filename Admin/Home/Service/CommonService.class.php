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
        return $this->getM()->where($where)->count();
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
        return M($this->getModelName());
    }

    /**
     * 得到D
     * @return Model
     */
    protected function getD() {
        return D($this->getModelName());
    }

    /**
     * 得到模型的名称
     * @return string
     */
    protected abstract function getModelName();
}
