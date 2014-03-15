<?php

namespace Home\Model;

use Think\Model;

/**
 * CommonModel
 * 数据库、数据表信息操作
 */
class CommonModel extends Model {
    /**
     * 得到数据表表信息
     * @params $tableName 数据表名称
     * @return array
     */
    public function getTablesInfo($tableName) {
        if (!isset($tableName)) {
            return $this->query('SHOW TABLE STATUS');
        }

        $tableInfo = $this->query("SHOW TABLE STATUS LIKE '{$tableName}'");
        return $tableInfo[0];
    }

    /**
     * 得到重建数据表的sql
     * @param  string $tableName
     * @return string
     */
    public function getRebuildTableSql($tableName) {
        $sql = $this->getDropTableSql($tableName) . "\r\n";
        $sql .= $this->getCreateTableSql($tableName) . "\r\n";

        return $sql;
    }

    /**
     * 得到建表信息
     * @param  string $tableName
     * @return string
     */
    public function getCreateTableSql($tableName) {
        if (!isset($tableName) || empty($tableName)) {
        	return '';
        }

        // 设置字段名加上`
        $this->query('SET SQL_QUOTE_SHOW_CREATE = 1');
        $createTableSql = $this->query("SHOW CREATE TABLE `{$tableName}`");
        return $createTableSql[0]['Create Table'] . ";";
    }

    /**
     * 数据表是否有记录
     * @param  string  $tableName
     * @return boolean
     */
    public function hasRecord($tableName) {
        $result = $this->query("SELECT COUNT(*) FROM {$tableName}");

        if ($result[0]['COUNT(*)']) {
            return true;
        }

        return false;
    }

    /**
     * 得到删除数据库的sql
     * @param  string $tableName
     * @return string
     */
    private function getDropTableSql($tableName) {
        return "DROP TABLE IF EXISTS `{$tableName}`;";
    }
}
