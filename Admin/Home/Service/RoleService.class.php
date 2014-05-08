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
