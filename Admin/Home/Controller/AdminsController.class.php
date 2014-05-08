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
        $result = $this->getPagination('Admin');

        $this->assign('admins', $result['data']);
        $this->assign('rows_count', $result['total_rows']);
        $this->assign('page', $result['show']);
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
     * 编辑管理员信息
     * @return
     */
    public function edit() {
        $adminService = D('Admin', 'Service');
        if (!isset($_GET['id']) || !$adminService->existAdmin($_GET['id'])) {
            return $this->error('需要编辑的管理员信息不存在！');
        }

        $admin = M('Admin')->getById($_GET['id']);

        $this->assign('admin', $admin);
        $this->assign('roles', $adminService->getRoles());
        $this->display();
    }

    /**
     * 更新管理员信息
     * @return
     */
    public function update() {
        $adminService = D('Admin', 'Service');
        if (!isset($_POST['admin'])
            || !$adminService->existAdmin($_POST['admin']['id'])) {
            return $this->errorReturn('无效的操作！');
        }

        $result = $adminService->update($_POST['admin']);
        if (!$result['status']) {
            return $this->errorReturn($result['data']['error']);
        }

        return $this->successReturn('更新管理员信息成功！', U('Admins/index'));
    }

    /**
     * 角色管理列表
     * @return
     */
    public function rolesIndex() {
        $roles = D('Admin', 'Service')->getRoles();

        $this->assign('roles', $roles);
        $this->assign('rows_count', count($roles));
        $this->display('roles_index');
    }

    /**
     * 添加角色
     * @return
     */
    public function roleAdd() {
        $roles = D('Admin', 'Service')->getRoles();

        $this->assign('roles', $roles);
        $this->display('role_add');
    }

    /**
     * 创建角色
     * @return
     */
    public function roleCreate() {
        if (!isset($_POST['role'])) {
            return $this->errorReturn('无效的操作！');
        }

        $result = D('Admin', 'Service')->addRole($_POST['role']);
        if (!$result['status']) {
            return $this->errorReturn($result['data']['error']);
        }

        return $this->successReturn('添加角色成功！', U('Admins/rolesIndex'));
    }

    /**
     * 编辑角色信息
     * @return
     */
    public function roleEdit() {
        $adminService = D('Admin', 'Service');
        if (!isset($_GET['id']) || !$adminService->existRole($_GET['id'])) {
            return $this->error('需要编辑的角色不存在！');
        }

        $role = M('Role')->getById($_GET['id']);

        $this->assign('role', $role);
        $this->assign('roles', $adminService->getRoles());
        $this->assign('sids', $adminService->getSonRoleIds($role['id']));
        $this->display('role_edit');
    }

    /**
     * 更新角色信息
     * @return
     */
    public function roleUpdate() {
        $adminService = D('Admin', 'Service');
        if (!isset($_POST['role'])
            || !$adminService->existRole($_POST['role']['id'])) {
            return $this->errorReturn('无效的操作！');
        }

        $result = $adminService->updateRole($_POST['role']);
        if (!$result['status']) {
            return $this->errorReturn($result['data']['error']);
        }

        return $this->successReturn('更新角色信息成功！', U('Admins/rolesIndex'));
    }
}
