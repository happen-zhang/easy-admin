<?php
namespace Home\Controller;

/**
 * InstallController
 */
class InstallController extends CommonController {
    /**
     * 当前数据库连接
     * @var handler
     */
    private $conn = null;

    /**
     * 当前安装进度
     * @var int
     */
    private $step = 0;

    /**
     * 当前数据表前缀
     * @var string
     */
    private $tablePrefix;

    /**
     * 安装表单
     * @return
     */
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
     * 正在安装
     * @return
     */
    public function installing() {
    	$data = json_encode($_POST);

        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 创建数据
     * @return
     */
    public function create() {
        $db = $_POST['db'];
        $db = array_filter($db, 'trim');
        $db['prefix'] = $db['prefix'] == '' ? C('DEFAULT_TABLE_PREFIX')
                                            : $db['prefix'];
        // 添加'_'作为分割
        if (false === strpos($db['prefix'], '_')) {
            $db['prefix'] .= '_';
        }
        $this->tablePrefix = $db['prefix'];

        // 当前已执行到的sql文件位置
        $this->step = intval($_GET['step']);
        if ($this->isComplete()) {
            // 安装完成
            exit();
        }

        // 连接数据库
        $this->conn = $this->connectDb($db);

        // Mysql版本不符合
        $this->invalidMysqlVersion();

        // 选择数据库
        $this->selectDb($db['name']);

        // 得到sql文件中的sql语句
        $sql = file_get_contents(C('SYSTEM_SQL_PATH'));
        $queries = sql_split($sql, $db['prefix']);
        // 执行sql
        $this->execSql($queries);

        if ($this->isComplete()) {
            // 安装完成
            exit();
        }

        // 插入 admin 数据
        $admin = $_POST['admin'];
        $admin = array_filter($admin, 'trim');
        $this->insertRootAdmin($admin);
        $this->closeDb();

        // 配置写入到文件中
        $this->saveConfig($_POST);

        // 安装完成
        $this->ajaxReturn(array('step' => 999999,
                                'info' => '安装完成'));
    }

    /**
     * 安装完成
     * @return
     */
    public function complete() {
        $this->display();
    }

    /**
     * 连接数据库
     * @param  array  $dbConfig
     * @return handler
     */
    private function connectDb(array $dbConfig) {
        $conn = mysql_connect($dbConfig['host'] . ':' . $dbConfig['port'],
                              $dbConfig['username'],
                              $dbConfig['password']);
        if (!$conn) {
            // 数据库连接失败
            $this->ajaxReturn(array('step' => 0,
                                    'info' => '数据库连接失败！'));
        }

        return $conn;
    }

    /**
     * 检测mysql版本是否过低
     * @return
     */
    private function invalidMysqlVersion() {
        $mysqlVersion = mysql_get_server_info($this->conn);
        if ($mysqlVersion < 4.1) {
            $this->closeDb();
            $this->ajaxReturn(array('step' => 0,
                                    'info' => '数据库版本过低！'));
        }
    }

    /**
     * 选择数据库
     * @param  string $dbName
     * @return
     */
    private function selectDb($dbName) {
        // 设置数据库字符集
        mysql_query('SET NAMES "utf8"');
        // 打开指定的数据库
        if (!mysql_select_db($dbName, $this->conn)) {
            // 指定数据库不存在，创建数据库
            if (!create_database($dbName, $this->conn)) {
                $this->closeDb();
                // 没有权限创建数据库
                $this->ajaxReturn(array('step' => 0,
                                        'info' => '没有权限创建数据库！'));
            }

            if ($this->step == 0) {
                $this->closeDb();

                // 创建数据库成功
                $data = array('step' => 1,
                              'info' => "成功创建数据库:{$dbName}<br>");
                $this->ajaxReturn($data);
            }
        }
    }

    /**
     * 执行sql
     * @param  array $queries
     * @return
     */
    private function execSql($queries) {
        $count = count($queries);

        for ($i = $this->step; $i < $count; $i++) {
            $sql = trim($queries[$i]);

            if (strstr($sql, 'CREATE TABLE')) {
                // CREATE TABLE
                preg_match('/CREATE TABLE `([^ ]*)`/', $sql, $matches);
                // 删除数据库
                mysql_query("DROP TABLE IF EXISTS `$matches[1]");

                if (mysql_query($sql)) {
                    $info = '<li>'. current_state_support("创建数据表{$matches[1]}完成") . '</li>';
                } else {
                    $info = '<li>'. current_state_support("创建数据表{$matches[1]}失败") . '</li>';
                }

                $this->closeDb();
                $this->ajaxReturn(array('step' => ++$i,
                                        'info' => $info));
            } else {
                // DROP TABLE 或 INSERT INTO
                mysql_query($sql);
            }
        }

        $this->step = $i;
    }

    /**
     * admin表中插入root
     * @param array $admin
     * @return
     */
    private function insertRootAdmin($admin) {
        $admin['uuid'] = uuid();
        $admin['time'] = datetime();

        $sql = "INSERT INTO `{$this->tablePrefix}admin` "
               . "VALUES (null, '{$admin['uuid']}', '{$admin['email']}','{$admin['password']}', '{$admin['time']}', '{$admin['time']}');";

        mysql_query($sql);
    }

    /**
     * 写入配置
     * @param  array  $dbConfig
     * @return fixed
     */
    private function saveConfig(array $systemConfig) {
        // 数据库配置
        $config['DB_TYPE'] = 'mysql';
        $config['DB_HOST'] = $systemConfig['db']['host'];
        $config['DB_NAME'] = $systemConfig['db']['name'];
        $config['DB_USER'] = $systemConfig['db']['username'];
        $config['DB_PWD'] = $systemConfig['db']['password'];
        $config['DB_PORT'] = $systemConfig['db']['port'];
        $config['DB_PREFIX'] = $systemConfig['db']['prefix'];

        // 站点配置
        $config['SITE_TITLE'] = $systemConfig['site']['title'];
        $config['SITE_KEYWORD'] = $systemConfig['site']['keyword'];
        $config['SITE_DESCRIPTION'] = $systemConfig['site']['description'];

        $data = "<?php return " . var_export($config, true) . ";\r\n";
        if (false === file_put_contents(C('SYSTEM_CONFIG_PATH'), $data)) {
            return false;
        }

        chmod(C('SYSTEM_CONFIG_PATH'), 0777);
        return true;
    }

    /**
     * 安装是否完成
     * @return boolean
     */
    private function isComplete() {
        if ($this->step == 999999) {
            return true;
        }

        return false;
    }

    /**
     * 关闭数据库连接
     * @return
     */
    private function closeDb() {
        if ($this->conn) {
            mysql_close($this->conn);
        }
    }
}
