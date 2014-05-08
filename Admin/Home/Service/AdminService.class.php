<?php
namespace Home\Service;

/**
 * AdminService
 */
class AdminService extends CommonService {
    /**
     * 节点类型
     * @var array
     */
    private $NODE_TYPE = array(
        1 => '应用（GROUP）',
        2 => '模块（MODULE）',
        3 => '操作（ACTION）'
    );

    /**
     * 添加管理员
     * @param  array $admin 管理员信息
     * @return array
     */
    public function add($admin) {
        $Admin = $this->getD();

        if (false === ($admin = $Admin->create($admin))) {
            return $this->errorResultReturn($Admin->getError());
        }

        if (false === $Admin->add($admin)) {
            return $this->errorResultReturn('系统错误！');
        }

        return $this->resultReturn(true);
    }

    /**
     * 更新管理员信息
     * @return
     */
    public function update($admin) {
        $Admin = $this->getD();

        if (false === ($admin = $Admin->create($admin))) {
            return $this->errorResultReturn($Admin->getError());
        }

        if (empty($admin['password'])) {
            unset($admin['password']);
        }

        if (false === $Admin->save($admin)) {
            return $this->errorResultReturn('系统错误！');
        }

        return $this->resultReturn(true);
    }

    /**
     * 添加角色
     * @param  array $role 角色信息
     * @return array
     */
    public function addRole($role) {
        $Role = D('Role');

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
        $Role = D('Role');

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
     * 管理员登录认证
     * @param  array $admin 管理员信息
     * @return array
     */
    public function login($admin) {
        $Admin = $this->getM();

        // 邮箱是否存在
        if (!$this->existAccount($admin['email'])) {
            return $this->errorResultReturn('邮箱不存在！');
        }

        $account = $Admin->getByEmail($admin['email']);
        // 密码验证
        if ($account['password'] != $this->encrypt($admin['password'])) {
            return $this->errorResultReturn('密码不正确！');
        }

        // 是否启用
        if (!$this->isActive($admin['email'])) {
            return $this->errorResultReturn('账户已被禁用！');
        }

        $loginMarked = C('LOGIN_MARKED');
        $shell = $this->genShell($account['id'], $account['password']);

        // 生成登录session
        $_SESSION[$loginMarked] = "{$shell}";

        // 生成登录cookie
        $shell .= '_' . time();
        setcookie($loginMarked, "{$shell}", 0, '/');
        $_SESSION['current_account'] = $account;

        // 权限认证
        if (C('USER_AUTH_ON')) {
            $_SESSION[C('USER_AUTH_KEY')] = $account['id'];
            if ($this->isSuperAdmin($account['email'])) {
                // 超级管理员无需认证
                $_SESSION[C('ADMIN_AUTH_KEY')] = true;
            }

            // 缓存访问权限
            \Org\Util\Rbac::saveAccessList();
        }

        // 更新最后登录时间
        $Admin->where("id={$account['id']}")
              ->save(array('last_login_at' => time()));

        return $this->resultReturn(true);
    }

    /**
     * 管理员登出
     * @return
     */
    public function logout() {
        $this->unsetLoginMarked();

        if (C('USER_AUTH_ON')) {
            unset($_SESSION[C('USER_AUTH_KEY')]);
            unset($_SESSION[C('ADMIN_AUTH_KEY')]);
        }

        session_destroy();
    }

    /**
     * 检查登录状态
     * @return array
     */
    public function checkLogin() {
        $loginMarked = C('LOGIN_MARKED');

        // 是否已登录
        if (!isset($_COOKIE[$loginMarked])) {
            return $this->errorResultReturn('尚未登录，请先进行登录！');
        }

        // 是否登录超时
        $cookie = explode('_', $_COOKIE[$loginMarked]);
        $timeout = C('LOGIN_TIMEOUT');
        if (time() > (end($cookie) + $timeout)) {
            $this->unsetLoginMarked();

            return $this->errorResultReturn('登录超时，请重新登录！');
        }

        // 是否帐号异常
        if ($cookie[0] != $_SESSION[$loginMarked]) {
            $this->unsetLoginMarked();

            return $this->errorResultReturn('账户异常，请重新登录！');
        }

        // 重新设置过期时间
        setcookie($loginMarked, $cookie[0] . '_' . time(), 0, '/');
        return $this->resultReturn(true);
    }

    /**
     * 加密数据
     * @param  string $data 需要加密的数据
     * @return string
     */
    public function encrypt($data) {
        return md5(C('AUTH_MASK') . md5($data));
    }

    /**
     * 生成登录shell
     * @param  int    $id       shell的id
     * @param  string $password shell的密码
     * @return string
     */
    private function genShell($id, $password) {
        return md5($password . C('AUTH_TOKEN')) . $id;
    }

    /**
     * 销毁登录标记
     * @return
     */
    private function unsetLoginMarked() {
        $loginMarked = C('LOGIN_MARKED');
        setcookie("{$loginMarked}", null, -3600, '/');
        unset($_SESSION[$loginMarked], $_COOKIE[$loginMarked]);

        return ;
    }

    /**
     * 得到带有层级的role数据
     * @return array
     */
    public function getRoles() {
        $category = new \Org\Util\Category('Role', array('id', 'pid', 'name'));
        return $category->getList();
    }

    /**
     * 得到带又层级的node数据
     * @return array
     */
    public function getNodes() {
        $category = new \Org\Util\Category('Node', array('id', 'pid','title'));
        return $category->getList();
    }

    /**
     * 得到节点的类型
     * @param  int    $type 节点的类型
     * @return string
     */
    public function getNodeType($type) {
        return $this->NODE_TYPE[$type];
    }

    /**
     * 得到子角色的id
     * @param  int   $id 角色id
     * @return array
     */
    public function getSonRoleIds($id) {
        $sRoles = M('Role')->field('id')->where("pid={$id}")->select();
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
     * 是否存在帐号
     * @param  string  $email email
     * @return boolean
     */
    public function existAccount($email) {
        if ($this->getM()->where("email='{$email}'")->count() > 0) {
            return true;
        }

        return false;
    }

    /**
     * 是否存在管理员
     * @param  int     $id 管理员id
     * @return boolean
     */
    public function existAdmin($id) {
        return !is_null($this->getM()->getById($id));
    }

    /**
     * 是否存在角色
     * @param  int     $id 角色id
     * @return boolean
     */
    public function existRole($id) {
        return !is_null(M('Role')->getById($id));
    }

    /**
     * 是否为超级管理员
     * @param  string  $email email
     * @return boolean
     */
    public function isSuperAdmin($email) {
        return $email == C('ADMIN_AUTH_KEY');
    }

    /**
     * 账户是否启用
     * @param  string  $email email
     * @return boolean
     */
    public function isActive($email) {
        $where = array(
            'email' => $email,
            'is_active' => 1
        );

        if ($this->getM()->where($where)->count() > 0) {
            return true;
        }

        return false;
    }

    protected function getModelName() {
        return 'Admin';
    }
}
