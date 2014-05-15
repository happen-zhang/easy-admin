<?php
namespace Home\Controller;

/**
 * DataController
 * 数据管理
 */
class DataController extends CommonController {
    /**
     * 数据备份
     * @return
     */
    public function backup() {
        $tablesInfo = D('Common')->getTablesInfo();
        $totalSize = 0;

        // 计算数据表大小
        foreach ($tablesInfo as $key => $tableInfo) {
            $tableSize = $tableInfo['Data_length']
                         + $tableInfo['Index_length'];
            $totalSize += $tableSize;
            $tablesInfo[$key]['size'] = bytes_format($tableSize);
        }

        $this->assign('tables_info', $tablesInfo);
        $this->assign('total_size', bytes_format($totalSize));
        $this->assign('table_num', count($tablesInfo));
        $this->display();
    }

    /**
     * 处理备份
     * @return
     */
    public function doBackup() {
        if (!IS_POST) {
            $this->errorReturn('访问出错！');
        }

        if (!isset($_POST['tables'])) {
            $this->errorReturn('请先选择需要备份的数据表！');
        }

        $dataLogic = D('Data', 'Logic');
        $result = $dataLogic->backup($_POST['tables']);

        if ($result['status'] !== $dataLogic::EXECUTE_FINISH) {
            return $this->errorReturn('无效的操作');
        }

        // 返回备份成功信息
        $info = '成功备份所选数据库表结构和数据，本次备份共生成了'
                . $result['data']['backuped_conut'] . "个SQL文件。"
                . '耗时：' . $result['data']['backuped_conut'] . '秒';

        $this->successReturn($info, U('Data/restore'));
    }

    /**
     * 数据导入
     * @return
     */
    public function restore() {
        $info = D('Data', 'Logic')->getBackupFilesInfo();

        $this->assign('total_size', $info['total_size']);
        $this->assign('info_list', $info['info_list']);
        $this->assign('files_count', count($info['info_list']));
        $this->display();
    }

    /**
     * 处理数据导入
     * @return
     */
    public function doRestore() {
        if (!IS_POST) {
            $this->errorReturn('访问出错！');
        }

        $dataLogic = D('Data', 'Logic');
        $result = $dataLogic->restore($_POST['file_prefix']);

        switch ($result['status']) {
            case $dataLogic::FILE_NOT_FOUND:
                $this->errorReturn('需要导入的文件不存在！');
                break ;
            
            case $dataLogic::EXECUTE_NOT_FINISH:
                $info = '如果导入SQL文件卷较大(多)导入时间可能需要几分钟甚至更久'
                        . '请耐心等待导入完成，导入期间请勿刷新本页，当前导入进度：'
                        . '<font color="red">已经导入'
                        . $result['data']['imported'] . '条Sql</font>';
                // 防止url缓存
                $url = U('Data/doRestore', array('rand_code' => rand_code(5)));
                // 返回json
                $this->successReturn($info, $url);
                break ;

            case $dataLogic::EXECUTE_FINISH:
                $info = "导入成功，耗时：{$result['data']['time']} 秒钟";
                $this->successReturn($info);
                break ;

            default:
                $this->errorReturn('无效的操作！');
                break ;
        }
    }

    /**
     * 文件下载
     * @return
     */
    public function downloadFile() {
        $info = '需要的下载的文件不存在或者已经被删除了！';
        $fileType = strtolower($_GET['file_type']);

        if (empty($_GET['file_name'])
            || empty($_GET['file_type'])
            || !in_array($fileType, array('zip', 'sql'))) {
            $this->error($info);
        }

        $backupConfig = C('BACKUP');
        $backupDir = ('zip' !== $fileType)
                     ? $backupConfig['BACKUP_DIR_PATH']
                     : $backupConfig['BACKUP_ZIP_DIR_PATH'];
        $filePath = $backupDir . $_GET['file_name'];

        if (!file_exists($filePath)) {
            $this->error($info);
        }

        $this->download($filePath, $_GET['file_name']);
    }

    /**
     * 删除sql文件
     * @return
     */
    public function deleteSqlFiles() {
        if (!IS_POST) {
            $this->errorReturn('无效的操作！');
        }

        if (!isset($_POST['sql_files'])) {
            $this->errorReturn('请选择需要删除的sql文件！');
        }

        $backupConfig = C('BACKUP');
        foreach ($_POST['sql_files'] as $file) {
            del_dir_or_file($backupConfig['BACKUP_DIR_PATH'] . $file);
        }

        $this->successReturn('已删除：' . implode("、", $_POST['sql_files']),
                             U('Data/restore', array('time' => time())));
    }

    /**
     * sql文件打包 
     * @return
     */
    public function zipSqlFiles() {
        if (!IS_POST) {
            $this->errorReturn('无效的操作！');
        }

        if (!isset($_POST['sql_files'])) {
            $this->errorReturn('请选择需要打包的sql文件');
        }

        $backupConfig = C('BACKUP');
        $sqlFiles = $_POST['sql_files'];
        // 保存的zip文件名称
        $zipName = $backupConfig['BACKUP_PREFIX'] . date('Y-m-d', time())
                   . '_' . rand_code(5) . '.zip';

        if (false === zip($sqlFiles,
                          $backupConfig['BACKUP_DIR_PATH'],
                          $backupConfig['BACKUP_ZIP_DIR_PATH'],
                          $zipName)) {
            $this->errorReturn('文件打包失败，需要打包的文件不存在或目录是不可写！');
        }

        $this->successReturn('文件打包完成！', U('Data/zipList'));
    }

    /**
     * 压缩文件列表
     * @return
     */
    public function zipList() {
        $zipFilesInfo = D('Data', 'Logic')->getZipFilesInfo();

        $this->assign('info_list', $zipFilesInfo['info_list']);
        $this->assign('total_size', $zipFilesInfo['total_size']);
        $this->assign('files_count', count($zipFilesInfo['info_list']));
        $this->display('zip_list');
    }

    /**
     * 解压zip文件
     * @return
     */
    public function unzipFiles() {
        if (!IS_POST) {
            return $this->errorReturn('无效的操作！');
        }

        $dataLogic = D('Data', 'Logic');
        $result = $dataLogic->unzipFiles($_POST['zip_files']);

        switch ($result['status']) {
            case $dataLogic::FILE_NOT_FOUND:
                $this->errorReturn('请选择需要解压的zip文件！');
                break ;

            case $dataLogic::EXECUTE_NOT_FINISH:
                $info = '正在解压缩请耐心等待，解压期间请勿刷新本页 '
                        . '<font color="red">当前已经解压完'
                        . "{$result['data']['file']}</font>";
                $url = U('Data/unzipFiles',
                         array('rand_code' => rand_code(5)));
                $this->successReturn($info, $url);
                break ;

            case $dataLogic::EXECUTE_FINISH:
                $info = "已解压完成，耗时：{$result['data']['time']} 秒";
                $this->successReturn($info, U('Data/restore'));
                break ;

            default:
                $this->errorReturn('无效的操作！');
                break ;
        }
    }

    /**
     * 删除sql文件
     * @return
     */
    public function deleteZipFiles() {
        if (!IS_POST) {
            $this->errorReturn('无效的操作！');
        }

        if (!isset($_POST['zip_files'])) {
            $this->errorReturn('请选择需要删除的zip文件！');
        }

        $backupConfig = C('BACKUP');
        foreach ($_POST['zip_files'] as $file) {
            del_dir_or_file($backupConfig['BACKUP_ZIP_DIR_PATH'] . $file);
        }

        $this->successReturn('已删除：' . implode("、", $_POST['zip_files']),
                             U('Data/zipList', array('time' => time())));
    }

    /**
     * 数据优化
     * @return
     */
    public function optimize() {
        $tablesInfo = D('Common')->getTablesInfo();
        $totalSize = array(
            'table' => 0,
            'data'  => 0,
            'index' => 0,
            'free'  => 0
        );

        foreach ($tablesInfo as $key => $tableInfo) {
            // 数据表大小
            $tableSize = $tableInfo['Data_length'] +$tableInfo['Index_length'];
            $tablesInfo[$key]['size'] = bytes_format($tableSize);
            $totalSize['table'] += $tableSize;
            
            // 数据大小
            $dataSize = $tableInfo['Data_length'];
            $tablesInfo[$key]['Data_length'] = bytes_format($dataSize);
            $totalSize['data'] += $dataSize;

            // 索引大小
            $indexSize = $tableInfo['Index_length'];
            $tablesInfo[$key]['Index_length'] = bytes_format($indexSize);
            $totalSize['index'] += $indexSize;

            // 碎片大小
            $freeSize = $tableInfo['Data_free'];
            $tablesInfo[$key]['Data_free'] = bytes_format($freeSize);
            $totalSize['free'] += $freeSize;
        }

        foreach ($totalSize as $key => $size) {
            $totalSize[$key] = bytes_format($size);
        }

        $this->assign('tables_info', $tablesInfo);
        $this->assign('total_size', $totalSize);
        $this->assign('tables_count', count($tablesInfo));
        $this->display();
    }

    /**
     * 优化、修复数据表
     * @return
     */
    public function doOptimize() {
        if (!IS_POST
            || !isset($_POST['action'])
            || !in_array($_POST['action'], array('optimize', 'repair'))) {
            $this->errorReturn('无效的操作！');
        }

        $M = D('Common');
        $tables = implode(',', $_POST['tables']);
        if ('optimize' == $_POST['action']) {
            if ($M->optimizeTables($tables)) {
                return $this->successReturn("优化表 {$tables} 成功！");
            }
        } else if ('repair' == $_POST['action']) {
            if ($M->repairTables($tables)) {
                return $this->successReturn("修复表 {$tables} 成功！");
            }
        }

        $this->errorReturn("操作失败！");
    }
}
