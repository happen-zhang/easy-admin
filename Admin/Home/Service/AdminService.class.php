<?php
namespace Home\Service;

/**
 * AdminService
 */
class AdminService extends CommonService {
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

        $account = $this->getM()->getByEmail($admin['email']);
        // 密码验证
        if ($account['password'] != $this->encrypt($admin['password'])) {
            return $this->errorResultReturn('密码不正确！');
        }

        $loginMarked = C('LOGIN_MARKED');
        $shell = $this->genShell($account['id'], $account['password']);

        // 生成登录session
        $_SESSION[$loginMarked] = "{$shell}";

        // 生成登录cookie
        $shell .= '_' . time();
        setcookie($loginMarked, "{$shell}", 0, "/");

        $_SESSION['current_account'] = $account;

        return $this->resultReturn(true);
    }

    /**
     * 管理员登出
     * @return
     */
    public function logout() {
        $this->unsetLoginMarked();
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

        // 已登录
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

    protected function getModelName() {
        return 'Admin';
    }
}
