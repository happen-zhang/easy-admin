<?php
namespace Home\Controller;

/**
 * DataController
 * 数据管理
 */
class DataController extends CommonController {
    /**
     * 数据信息
     * @return
     */
    public function index(){
        $this->display();
    }

    /**
     * 数据备份
     * @return
     */
    public function backup() {
        $this->display();
    }

    /**
     * 数据恢复
     * @return
     */
    public function restore() {
        $this->display();
    }

    /**
     * 数据压缩
     * @return
     */
    public function unpack() {
        $this->display();
    }

    /**
     * 数据优化
     * @return
     */
    public function optimize() {
        $this->display();
    }
}
