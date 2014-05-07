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
        $this->assign('roles', D('Admin', 'Service')->getRoles());
        $this->display();
    }

    /**
     * 创建管理员
     * @return
     */
    public function create() {
        if (!isset($_POST['admin'])) {
            return $this->errorReturn('无效的操作！');
        }

        $result = D('Admin', 'Service')->add($_POST['admin']);
        if (!$result['status']) {
            return $this->errorReturn($result['data']['error']);
        }

        return $this->successReturn('添加管理员成功！', U('Admins/index'));
    }

    /**
     * 编辑管理员
     * @return
     */
    public function edit() {
        $this->display();
    }
}
