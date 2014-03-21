<?php

namespace Home\Logic;

/**
 * DataLogic
 */
class DataLogic extends CommonLogic {
    /**
     * 文件未找到
     */
    const FILE_NOT_FOUND = 1;
    /**
     * 执行未完成
     */
    const EXECUTE_NOT_FINISH = 2;
    /**
     * 执行完成
     */
    const EXECUTE_FINISH = 3;

    /**
     * 备份数据表
     * @param  array $tables
     * @return array
     */
    public function backup($tables) {
        // 防止数据库备份超时
        function_exists('set_time_limit') && set_time_limit(0);

        // 数据表操作模型
        $M = D('Common');
        // 备份开始时间
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

        $data['time'] = time() - $time;
        $data['backuped_conut'] = --$fileNo;
        return $this->resultReturn(self::EXECUTE_FINISH, $data);
    }

    /**
     * 得到备份文件的信息
     * @return array
     */
    public function getBackupFilesInfo() {
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

    /**
     * 恢复sql文件数据
     * @param  string $filePrefix
     * @return int
     */
    public function restore($filePrefix) {
        // 设置导入数据不超时
        function_exists('set_time_limit') && set_time_limit(0);

        $M = M();
        $backupConfig = C('BACKUP');
        // 得到需要导入的文件
        $restoreFiles = isset($_SESSION['restore_cache'])
                        ? $_SESSION['restore_cache']['files']
                        : $this->getRestoreFiles($filePrefix);

        if (empty($restoreFiles)) {
            return $this->resultReturn(self::FILE_NOT_FOUND, null);
        }

        // 保存需要导入的文件
        $_SESSION['restore_cache']['files'] = $restoreFiles;
        // 导入开始时间
        if (!isset($_SESSION['restore_cache']['time'])) {
            $_SESSION['restore_cache']['time'] = time();
        }
        // 文件已读指针位置
        $position = 0;
        if (isset($_SESSION['restore_cache']['position'])) {
            $position = $_SESSION['restore_cache']['position'];
        }

        $sql = '';
        $exexuted = 0;
        foreach ($restoreFiles as $key => $storeFile) {
            $filePath = $backupConfig['BACKUP_DIR_PATH'] . $storeFile;
            if (!file_exists($filePath)) {
                // 需要导入的文件不存在
                continue ;
            }
            $fp = fopen($filePath, 'r');
            // 文件指针移动
            fseek($fp, $position);
            // 读取文件中的sql
            while (!feof($fp)) {
                // 读出一行数据
                $line = trim(fgets($fp));
                // 是否为sql语句而不是注释
                if (!$this->isSql($line)) {
                    continue ;
                }

                $sql .= $line;
                if (';' !== $line[strlen($line) - 1]) {
                    // 不是完整的sql则不执行
                    continue ;
                }

                // 执行完整的sql
                $M->query($sql);
                $sql = '';
                $exexuted++;
                // 如果执行sql超过或等于500行，则返回提示信息
                if ($exexuted >= 500) {
                    // 保存读出文件指针位置
                    $_SESSION['restore_cache']['position'] = ftell($fp);
                    // 已导入行数
                    $imported = isset($_SESSION['restore_cache']['imported'])
                                ? $_SESSION['restore_cache']['imported'] : 0;
                    $imported += $exexuted;
                    $_SESSION['restore_cache']['imported'] = $imported;
                    fclose($fp);

                    return $this->resultReturn(self::EXECUTE_NOT_FINISH,
                                               array('imported' => $imported));
                }
            }
            fclose($file);
            // 删除已导入完成的文件
            unset($_SESSION['restore_cache']['files'][$key]);
            $position = 0;
        }
        // 导入执行时间
        $time = time() - $_SESSION['restore_cache']['time'];
        unset($_SESSION['restore_cache']);
        
        return $this->resultReturn(self::EXECUTE_FINISH,
                                   array('time' => $time));
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
     * 得到需要导入的sql文件
     * @return array
     */
    public function getRestoreFiles($filePrefix) {
        if (empty($filePrefix)) {
            return array();
        }

        $backupConfig = C('BACKUP');
        $dirHandle = opendir($backupConfig['BACKUP_DIR_PATH']);
        $files = array();
        while ($file = readdir($dirHandle)) {
            if (preg_match('/\.sql$/i', $file)
                && preg_match('/' . $filePrefix . '/i', $file)) {
                $files[] = $file;
            }
        }
        closedir($dirHandle);

        // 文件没有匹配成功，则说文件不存在
        if (0 === count($files)) {
            return array();
        }

        $sqlFiles = array();
        foreach ($files as $file) {
            // 取出分卷号作为键值
            $key = str_replace('.sql', '',
                               str_replace($filePrefix . '_', '', $file));
            $sqlFiles[$key] = $file;
        }
        unset($files, $filePrefix);
        // 按照分卷号排序
        ksort($sqlFiles);

        return $sqlFiles;
    }

    /**
     * 得到zip文件列表信息
     * @return array
     */
    public function getZipFilesInfo() {
        $backupConfig = C('BACKUP');
        $zipDir = $backupConfig['BACKUP_ZIP_DIR_PATH'];
        $dirHandle = opendir($zipDir);

        $zipFilesInfo = array();
        $totalSize = 0;
        while ($file = readdir($dirHandle)) {
            // 是否为zip文件
            if (preg_match('/\.zip$/i', $file)) {
                $info = array();
                $info['file'] = $file;
                $info['size'] = filesize($zipDir . $file);
                $totalSize += $info['size'];
                $info['size'] = bytes_format($info['size']);
                $info['time'] =filectime($zipDir .$file);
                $zipFilesInfo[$info['time']] = $info;
            }
        }

        // 时间逆序排序
        arsort($zipFilesInfo);
        foreach ($zipFilesInfo as $key => $info) {
            $zipFilesInfo[$key]['time'] = date('Y-m-d H:i:s', $info['time']);
        }

        return array('info_list' => $zipFilesInfo,
                     'total_size' => bytes_format($totalSize));        
    }

    /**
     * 解压zip文件到备份目录
     * @param  array $zipFiles 需要解压的zip文件
     * @return array
     */
    public function unzipFiles($zipFiles) {
        if (!isset($_SESSION['unzip']) && !isset($zipFiles)) {
            return $this->resultReturn(self::FILE_NOT_FOUND);
        }

        $backupConfig = C('BACKUP');
        if (isset($_SESSION['unzip'])) {
            $zipFiles = $_SESSION['unzip']['files'];
        } else {
            $_SESSION['unzip']['time'] = time();
            $zipFiles = $_POST['zip_files'];
            $_SESSION['unzip']['files'] = $zipFiles;
            $_SESSION['unzip']['count'] = count($zipFiles);
        }

        // 解压zip文件到备份目录
        $zipFile = array_shift($zipFiles);
        unzip($backupConfig['BACKUP_ZIP_DIR_PATH'] . $zipFile,
              $backupConfig['BACKUP_DIR_PATH']);

        if (count($zipFiles) > 0) {
            $_SESSION['unzip']['files'] = $zipFiles;
            return $this->resultReturn(self::EXECUTE_NOT_FINISH,
                                       array('file' => $zipFile));
        }

        $time = time() - $_SESSION['unzip']['time'];
        unset($_SESSION['unzip']);
        return $this->resultReturn(self::EXECUTE_FINISH,
                                   array('time' => $time));
    }

    /**
     * 检查是否为一条sql
     * @param  string  $sql
     * @return boolean
     */
    public function isSql($sql) {
        if (empty($sql)
        	|| '#' == $sql[0]
        	|| ('-' == $sql[0] && '-' == $sql[1])) {
            return false;
        }

        return true;
    }

    /**
     * 返回结果值
     * @param  int $status
     * @param  fixed $data
     * @return array
     */
    private function resultReturn($status, $data) {
        return array('status' => $status,
                     'data' => $data);
    }
}
