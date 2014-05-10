<?php
namespace Home\Controller;

/**
 * RolesController
 * 角色信息
 */
class RolesController extends CommonController {
    /**
     * 角色管理列表
     * @return
     */
    public function index() {
        $roles = D('Role', 'Service')->getRoles();

        $this->assign('roles', $roles);
        $this->assign('rows_count', count($roles));
        $this->display();
    }

    /**
     * 添加角色
     * @return
     */
    public function add() {
        $this->assign('roles', D('Role', 'Service')->getRoles());
        $this->display();
    }

    /**
     * 创建角色
     * @return
     */
    public function create() {
        if (!isset($_POST['role'])) {
            return $this->errorReturn('无效的操作！');
        }

        $result = D('Role', 'Service')->addRole($_POST['role']);
        if (!$result['status']) {
            return $this->errorReturn($result['data']['error']);
        }

        return $this->successReturn('添加角色成功！', U('Roles/index'));
    }

    /**
     * 编辑角色信息
     * @return
     */
    public function edit() {
        $roleService = D('Role', 'Service');
        if (!isset($_GET['id']) || !$roleService->existRole($_GET['id'])) {
            return $this->error('需要编辑的角色不存在！');
        }

        $role = M('Role')->getById($_GET['id']);

        $this->assign('role', $role);
        $this->assign('roles', $roleService->getRoles());
        $this->assign('sids', $roleService->getSonRoleIds($role['id']));
        $this->display();
    }

    /**
     * 更新角色信息
     * @return
     */
    public function update() {
        $roleService = D('Role', 'Service');
        if (!isset($_POST['role'])
            || !$roleService->existRole($_POST['role']['id'])) {
            return $this->errorReturn('无效的操作！');
        }

        $result = $roleService->updateRole($_POST['role']);
        if (!$result['status']) {
            return $this->errorReturn($result['data']['error']);
        }

        return $this->successReturn('更新角色信息成功！', U('Roles/index'));
    }


    /**
     * 权限分配
     * @return
     */
    public function assignAccess() {
        $roleService = D('Role', 'Service');
        if (!isset($_GET['id'])
            || !$roleService->existRole($_GET['id'])) {
            return $this->errorReturn('需要分配权限的角色不存在！');
        }

        $role = M('Role')->getById($_GET['id']);
        if (0 == $role['pid']) {
            return $this->error('您无权限进行该操作！');
        }

        $access = D('Access')->relation(true)
                             ->where("role_id={$role['id']}")
                             ->select();
        $rAccess = array();
        foreach ($access as $key => $item) {
            $rAccess[$key]['val'] = "{$item['node_id']}:"
                                    . "{$item['node']['level']}:"
                                    . "{$item['node']['pid']}";
        }

        $role['access'] = json_encode($rAccess);

        $this->assign('role', $role);
        $this->assign('nodes', D('Node', 'Service')->getLevelNodes());
        $this->display('assign_access');
    }

    /**
     * 处理分配权限
     * @return
     */
    public function doAssignAccess() {
        $roleService = D('Role', 'Service');
        if (!isset($_POST['id']) || !$roleService->existRole($_POST['id'])) {
            return $this->errorReturn('需要分配权限的角色不存在！');
        }

        if (empty($_POST['access'])) {
            $_POST['access'] = array();
        }

        $result = $roleService->assignAccess($_POST['id'], $_POST['access']);
        if (!$result['status']) {
            return $this->errorReturn($result['data']['error']);
        }

        if (!empty($result['data'])) {
            return $this->successReturn($result['data']);
        }

        return $this->successReturn('分配权限成功！');
    }
}
