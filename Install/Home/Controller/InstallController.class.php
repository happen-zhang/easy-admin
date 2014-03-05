<?php
namespace Home\Controller;

/**
 * InstallController
 */
class InstallController extends CommonController {
    public function index() {
        $this->display();
    }

    /**
     * 检查数据库连接
     * @return
     */
    public function checkDbConnect() {
        if (!isset($_POST['db']['password'])) {
            return $this->ajaxReturn(false);
        }

        $host = $_POST['db']['host'] . ':' . $_POST['db']['port'];
        $conn = mysql_connect($host,
                              $_POST['db']['username'],
                              $_POST['db']['password']);

        if (!$conn) {
            $this->ajaxReturn(false);
        }

        mysql_close($conn);
        $this->ajaxReturn(true);
    }

    /**
     * 创建数据
     * @return
     */
    public function create() {
        $this->display('installing');
    }
}
