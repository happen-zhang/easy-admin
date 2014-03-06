<?php
namespace Home\Controller;

/**
 * DataController
 * 数据管理
 */
class DataController extends CommonController {
    /**
     * 数据信息
     * @return
     */
    public function index(){
        $this->display();
    }

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
     * 数据恢复
     * @return
     */
    public function restore() {
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
}
