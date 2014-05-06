<?php
namespace Home\Controller;

/**
 * AdminsController
 * 管理员信息
 */
class AdminsController extends CommonController {
	  /**
	   * 管理员列表
	   * @return
	   */
    public function index() {
        $this->display();
    }

    /**
     * 添加管理员
     * @return
     */
    public function add() {
        $this->display();
    }

    /**
     * 编辑管理员
     * @return
     */
    public function edit() {
        $this->display();
    }
}
