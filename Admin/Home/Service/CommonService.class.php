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
