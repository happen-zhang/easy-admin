<?php
namespace Home\Service;

/**
 * RoleService
 */
class RoleService extends CommonService {
    /**
     * 添加角色
     * @param  array $role 角色信息
     * @return array
     */
    public function addRole($role) {
        $Role = $this->getD();

        if (false === ($role = $Role->create($role))) {
            return $this->errorResultReturn($Role->getError());
        }

        if (false === $Role->add($role)) {
            return $this->errorResultReturn('系统错误！');
        }

        return $this->resultReturn(true);
    }

    /**
     * 更新角色信息
     * @return
     */
    public function updateRole($role) {
        $Role = $this->getD();

        if (false === ($role = $Role->create($role))) {
            return $this->errorResultReturn($Role->getError());
        }

        if ($role['id'] == $role['pid']) {
            $role['pid'] = 0;
        }

        if (false === $Role->save($role)) {
            return $this->errorResultReturn('系统错误！');
        }

        return $this->resultReturn(true);
    }

    /**
     * 分配角色权限
     * @param  int   $roleId 角色id
     * @param  array $access 权限访问数组
     * @return array
     */
    public function assignAccess($roleId, array $access) {
        $Access = M('Access');

        $Access->startTrans();
        $Access->where("role_id={$roleId}")->delete();
        if (0 === count($access)) {
            $Access->commit();
            return $this->resultReturn(true, '清楚数据成功！');
        }

        $newAccess = array();
        foreach ($access as $item) {
            $item = explode(':', $item);
            $newAccess[] = array('role_id' => $roleId, 'node_id' => $item[0]);
        }

        // 插入新权限
        if (false === $Access->addAll($newAccess)) {
            $Access->rollback();
            return $this->errorResultReturn('分配权限失败！');
        }

        $Access->commit();
        return $this->resultReturn(true);
    }

    /**
     * 得到带有层级的role数据
     * @return array
     */
    public function getRoles() {
        $category = new \Org\Util\Category($this->getModelName(),
                                           array('id', 'pid', 'name'));
        return $category->getList();
    }

    /**
     * 得到子角色的id
     * @param  int   $id 角色id
     * @return array
     */
    public function getSonRoleIds($id) {
        $sRoles = $this->getM()->field('id')->where("pid={$id}")->select();
        $sids = array();

        if (is_null($sRoles)) {
            return $sids;
        }

        foreach ($sRoles as $sRole) {
            $sids[] = $sRole['id'];
            $sids = array_merge($sids, $this->getSonRoleIds($sRole['id']));
        }

        return $sids;
    }

    /**
     * 是否存在角色
     * @param  int     $id 角色id
     * @return boolean
     */
    public function existRole($id) {
        return !is_null($this->getM()->getById($id));
    }

    protected function getModelName() {
        return 'Role';
    }
}
