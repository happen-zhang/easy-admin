<?php
namespace Home\Controller;

/**
 * PublicController
 * 公开页面访问接口
 */
class PublicController extends CommonController {
    /**
    * 初始化
    * @return
    */
    public function _initialize() {
        parent::_initialize();

        // 开启令牌
        if (ACTION_NAME == 'index') {
            C('TOKEN_ON', true);
        }

        // 需要登录才能访问的action
        $filterLogin = array('logout');
        if (in_array(ACTION_NAME, $filterLogin)) {
            $this->filterLogin();
        }

        // 登录后不可访问的action
        $filterAction = array('index', 'login');
        if (in_array(ACTION_NAME, $filterAction) && $this->hasLogin()) {
            return $this->redirect('Index/index');
        }
    }

    /**
     * 管理员登录页
     * @return
     */
    public function index() {
        layout(false);
        $this->display();
    }

    /**
     * 管理员登录
     * @return
     */
    public function login() {
        $reloadUrl = U('Public/index');

        if (empty($_POST['admin']['email'])) {
            return $this->errorReturn('请填写登录邮箱！', $reloadUrl);
        }

        if (empty($_POST['admin']['password'])) {
            return $this->errorReturn('请填写登录密码！', $reloadUrl);
        }

        if (!M('Admin')->autoCheckToken($_POST)) {
            return $this->errorReturn('登录令牌超时！', $reloadUrl);
        }

        if (!check_verify_code($_POST['verify_code'])) {
            return $this->errorReturn('验证码不正确！', $reloadUrl);
        }

        $adminService = D('Admin', 'Service');
        $admin = $_POST['admin'];
        // 登录认证
        $result = $adminService->login($admin);

        if (!$result['status']) {
            return $this->errorReturn($result['data']['error'], $reloadUrl);
        }

        return $this->successReturn('登录成功！', U('Index/index'));
    }

    /**
     * 管理员登出
     * @return
     */
    public function logout() {
        D('Admin', 'Service')->logout();

        $this->success('登出成功！', U('Public/index'));
    }

    /**
     * 发送找回密码的邮件
     * @return
     */
    public function sendFindPwdMail() {
        $adminService = D('Admin', 'Service');
        if (!isset($_POST['admin']['email'])
            || !$adminService->existAccount($_POST['admin']['email'])) {
            return $this->errorReturn('登录邮箱不存在！');
        }

        $email = $_POST['admin']['email'];
        $admin = M('Admin')->getByEmail($email);
        $randCode = rand_code(5);
        $hash = $admin['id'] . md5($randCode);

        $config = C('MAIL');
        $target = U('Public/findPassword', array('hash' => $hash));
        $url = $_SERVER['HTTP_HOST'] . $target;
        $body = str_replace('?', $url, $config['MAIL_BODY']);

        // 发送邮件
        $result = smtp_mail($email, $email, C('SITE_TITLE'), $body, $config);

        if (true !== $result) {
            return $this->errorReturn('系统出错了，请稍后再试！');
        }

        $admin['mail_hash'] = $hash;
        M('Admin')->save($admin);

        $info = "密码重置邮件已发，请到{$admin['email']}查收！";
        return $this->successReturn($info);
    }

    /**
     * 找回密码
     * @return
     */
    public function findPassword() {
        $hash = substr($_GET['hash'], -32);
        $id = (int)str_replace($hash, '', $_GET['hash']);

        if (!D('Admin', 'Service')->hasSendMail($id, $_GET['hash'])) {
            return $this->error('地址不存在或者已经失效了！', U('Public/index'));
        }

        $admin = M('Admin')->getById($id);

        layout(false);
        $this->assign('admin', $admin);
        $this->assign('hash', $_GET['hash']);
        $this->display('find_password');
    }

    /**
     * 重置密码
     * @return
     */
    public function resetPassword() {
        $Admin = D('Admin');
        $admin = $Admin->getByMailHash($_POST['hash']);

        if (is_null($admin)) {
            return $this->errorReturn('无效的操作！');
        }

        $admin['mail_hash'] = '';
        $admin['password'] = $_POST['password'];
        $admin['cfm_password'] = $_POST['cfm_password'];

        $admin = $Admin->create($admin);
        if (false === $admin) {
            return $this->errorReturn($Admin->getError());
        }

        if (false === $Admin->save($admin)) {
            return $this->errorReturn('系统出错了！');
        }

        $url = U('Public/index');
        return $this->successReturn('重置密码成功，跳转登录！', $url);
    }

    /**
     * 验证码图片
     * @return
     */
    public function verifyCode() {
        $config = array(
            'imageW' => 60,
            'imageH' => 30,
            'fontSize' => 8,
            'length' => 4,
            'useNoise' => false,
            'codeSet' => '0123456789'
        );
        create_verify_code($config);
    }
}
