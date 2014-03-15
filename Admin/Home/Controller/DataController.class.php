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
        $tablesInfo = M()->query('SHOW TABLE STATUS');
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
            $this->errorReturn('访问出错');
        }

        if (!isset($_POST['tables'])) {
            $this->errorReturn('请先选择需要备份的数据表');
        }

        // 防止数据库备份超时
        function_exists('set_time_limit') && set_time_limit(0);

        $M = D('Common');
        $tables = $_POST['tables'];
        $time = time();

        // 备份配置
        $backupConfig = C('BACKUP');
        // 备份文件路径
        $sqlFile = $backupConfig['BACKUP_DIR_PATH']
                   . $backupConfig['BACKUP_PREFIX']
                   . date('Ymd') . '_'
                   . rand_code($backupConfig['BACKUP_FILE_CODE_LENGTH']);
        // 分卷行数
        $sqlListRows = $backupConfig['BACKUP_SQL_LIST_ROWS'];

        // 备份文件注释头
        $descName = $backupConfig['BACKUP_DESCRIPTION_NAME'];
        $descUrl = $backupConfig['BACKUP_DESCRIPTION_URL'];
        $sqlFileHeader = $this->getSqlFileHeaderInfo($descName, $descUrl);
        // 备份文件注释头的长度
        $headerLen = strlen($sqlFileHeader);

        $output = '';
        // 重建数据表的sql
        $rebuildSql = $this->getRebuildTablesSql($tables);
        // 分卷数
        $fileNo = 1;
        // 已备份的数据库
        $backuped = array();
        foreach ($tables as $table) {
            $backuped[] = $table;
            $output .= "\r\n# 数据表：{$table} 数据信息\r\n";
            $output .= $M->hasRecord($table) ? "\r\n" : "# 没有数据记录\r\n\r\n";

            // 得到数据表的信息
            $tableInfo = $M->getTablesInfo($table);
            // 得到sql的分页数
            $page = ceil($tableInfo['Rows'] / $sqlListRows) - 1;
            // 按分页获取sql数据
            for ($i = 0; $i <= $page; $i++) {
                // 当前页中的$sqlListRows行数据
                $rows = $this->queryTable($table,
                                          $i * $sqlListRows,
                                          $sqlListRows);
                // 
                foreach ($rows as $row) {
                    // 得到insert sql
                    $insertSql = $this->getInsertValueSql($table, $row);
                    $insertSql .= "\r\n";
                    // sql备份文件的基本数据信息
                    $sqlInfo = $this->getSqlFileInfo($fileNo,
                                                     $tables,
                                                     $backuped);
                    // 当前文件长度
                    $currentFileLen = strlen($sqlFileHeader
                                             . $sqlInfo
                                             . $rebuildSql
                                             . $output
                                             . $insertSql);
                    if ($currentFileLen > $backupConfig['SQL_FILE_SIZE']) {
                        // 达到分卷大小，写出备份文件
                        $file = $sqlFile . '_' . $fileNo . '.sql';
                        // 分卷为1,则需要写出重建表信息
                        $temp = $output;
                        $output = $sqlFileHeader . $sqlInfo;
                        $output .= (1 == $fileNo) ? $rebuildSql : '';
                        $output .= $temp;
                        // 写出sql文件
                        file_put_contents($file, $output, FILE_APPEND);
                        $rebuildSql = $output = '';
                        $backuped = array();
                        $backuped[] = $table;
                        $fileNo++;
                    }

                    $output .= $insertSql;
                }
            }
        }

        // 写出最后一个sql文件分卷
        if (strlen($rebuildSql . $output) > 0) {
            $sqlInfo = $this->getSqlFileInfo($fileNo, $tables, $backuped);
            $file = $sqlFile . '_' . $fileNo . '.sql';
            // 组装output
            $temp = $output;
            $output = $sqlFileHeader . $sqlInfo;
            $output .= (1 == $fileNo) ? $rebuildSql : '';
            $output .= $temp;
            // 写出sql文件
            file_put_contents($file, $output, FILE_APPEND);
            $fileNo++;
        }

        // 返回备份成功信息
        $time = (time() - $time) / 1000;
        $info = '成功备份所选数据库表结构和数据，本次备份共生成了'
                . ($fileNo - 1) . "个SQL文件。耗时：{$time} 秒";

        $this->successReturn($info, U('Data/restore'));
    }

    /**
     * 数据恢复
     * @return
     */
    public function restore() {
        $info = $this->getBackupFilesInfo();

        $this->assign('total_size', $info['total_size']);
        $this->assign('info_list', $info['info_list']);
        $this->assign('files_count', count($info['info_list']));
        $this->display();
    }

    /**
     * 数据压缩
     * @return
     */
    public function unpack() {
        $this->display();
    }

    /**
     * 数据优化
     * @return
     */
    public function optimize() {
        $this->display();
    }

    /**
     * 得到数据表的重建sql
     * @param  array  $tables
     * @return string
     */
    private function getRebuildTablesSql(array $tables) {
        if (empty($tables)) {
            return '';
        }

        $M = D('Common');
        $sql = '';
        foreach ($tables as $table) {
            $sql .= "# 数据库表：{$table} 结构信息\r\n";
            $sql .= $M->getRebuildTableSql($table) . "\r\n";
        }

        return $sql;
    }

    /**
     * 取出指定数据表中的数据
     * @param  string $tableName
     * @param  int $skip
     * @param  int $rows
     * @return array
     */
    private function queryTable($tableName, $skip, $rows) {
        $sql = "SELECT * FROM {$tableName} LIMIT {$skip}, {$rows};";
        return M()->query($sql);
    }

    /**
     * 从数组中的值组件insert sql中的values()部分
     * @param  array  $row
     * @return string
     */
    private function getInsertValueSql($table, array $row) {
        $isFirst = true;
        $valuesSql = '';

        // 得到一行数据中的值
        foreach ($row as $val) {
            $valuesSql .= $isFirst ? '' : ',';
            $valuesSql .= ($val == '') ? "''" : "'{$val}'";
            $isFirst = false;
        }

        return "INSERT INTO `{$table}` VALUES ({$valuesSql});";
    }

    /**
     * 得到sql备份文件的头信息
     * @param  string $descName
     * @param  string $descUrl
     * @return string
     */
    private function getSqlFileHeaderInfo($descName, $descUrl) {
        return "# --------------------------------------- \r\n"
               . '# ' . $descName . " \r\n"
               . '# ' . $descUrl . " \r\n";
    }

    /**
     * 得到sql文件分卷的描述
     * @param  int $fileNo
     * @param  array $tables
     * @param  array $backuped
     * @return string
     */
    private function getSqlFileInfo($fileNo, $tables, $backuped) {
        $sqlInfo =  "\r\n# Time: " . date('Y-m-d H:i:s') . " \r\n"
                    . "# --------------------------------------- \r\n"
                    . "# 当前SQL卷标：#{$fileNo}\r\n"
                    . "# --------------------------------------- \r\n\r\n\r\n";

        if (1 == $fileNo) {
            // 分卷为1，则需要声明该sql文件内的包含的所有表名
            $sqlInfo = "# Description:当前SQL文件包含了表："
                       . implode('、', $tables) . "的结构信息，表："
                       . implode('、', $backuped) . "的数据" . $sqlInfo;
        } else {
            $sqlInfo = "# Description:当前SQL文件包含了表："
                       . implode("、", $backuped) . "的数据" . $sqlInfo;
        }

        return $sqlInfo;
    }

    /**
     * 得到备份文件的信息
     * @return array
     */
    private function getBackupFilesInfo() {
        $backupConfig = C('BACKUP');
        $dirPath = $backupConfig['BACKUP_DIR_PATH'];
        $readLength = $backupConfig['BACKUP_DESCRIPTION_LENGTH'];

        // 备份文件目录
        $dirHandle = opendir($dirPath);
        $backupList = array();
        // 备份文件的总大小
        $totalSize = 0;
        while ($file = readdir($dirHandle)) {
            if (preg_match('/\.sql$/i', $file)) {
                $filePath = $dirPath . '/' . $file;
                // 匹配sql文件
                $fp = fopen($filePath, 'rb');
                // 只读出sql文件的头信息
                $sqlFileInfo = fread($fp, $readLength);
                fclose($fp);
                // 取出每行数据
                $detail = explode("\n", $sqlFileInfo);
                $backupFile = array();
                $backupFile['name'] = $file;
                $backupFile['url'] = substr($detail[2], 10);
                $backupFile['description'] = substr($detail[3], 14);
                $backupFile['time'] = substr($detail[4], 8);
                // 文件大小
                $size = filesize($filePath);
                $backupFile['size'] = bytes_format($size);
                $backupFile['prefix'] = substr($file, 0, strrpos($file, '_'));
                // sql文件分卷号
                $startPos = strrpos($file, '_') + 1;
                $fileNoLen = strrpos($file, '.') - 1 - strrpos($file, '_');
                $backupFile['file_no'] = substr($file, $startPos, $fileNoLen);
                // 文件创建或修改时间
                $backupList[filemtime($filePath)][$file] = $backupFile;
                $totalSize += $size;
            }
        }
        closedir($dirHandle);

        // 备份时间逆序排序
        ksort($backupList);
        $infoList = array();
        foreach ($backupList as $sqlFileInfos) {
            // 文件名排序
            ksort($sqlFileInfos);
            foreach ($sqlFileInfos as $sqlFileInfo) {
                $infoList[] = $sqlFileInfo;    
            }
        }
        unset($backupList);

        return array('info_list' => $infoList,
                     'total_size' => bytes_format($totalSize));
    }
}
