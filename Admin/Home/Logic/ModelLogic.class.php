<?php

namespace Home\Logic;

/**
 * ModelLogic
 */
class ModelLogic extends CommonLogic {
    /**
     * 为模型创建数据表
     * 自动生成created_at、updated_at字段
     * @param  string  $tableName 数据表名称
     * @param  boolean $hasPk     是否含有主键
     * @param  string  $engine    引擎类型
     * @return boolean            是否创建成功
     */
    public function createTable($tableName,
                                   $hasPk,
                                   $engine = 'InnoDB',
                                   $comment) {
        $pkSql = '';
        if ($hasPk) {
            // id主键的sql
            $pkSql = "`id` int PRIMARY KEY NOT NULL "
                     . "AUTO_INCREMENT COMMENT  '主键',";
        }

        $sql = "CREATE TABLE `{$tableName}` ("
                . $pkSql
                . "`created_at` datetime NOT NULL COMMENT '创建时间',"
                . "`updated_at` datetime NOT NULL COMMENT '更新时间'"
                . ") ENGINE={$engine} CHARSET=utf8 COMMENT='{$comment}'";

        // 创建数据表
        if (false === M()->query($sql)) {
            return false;
        }

        return true;
    }
}
