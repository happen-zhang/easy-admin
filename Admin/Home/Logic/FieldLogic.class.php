<?php

namespace Home\Logic;

/**
 * FieldLogic
 */
class FieldLogic extends CommonLogic {
    /**
     * 重置field长度
     * @param  array  $field
     * @return
     */
    public function resetLength(array &$field) {
        switch ($field['type']) {
            case 'TINYINT':
            case 'SMALLINT':
            case 'INT':
            case 'BIGINT':
            case 'CHAR':
            case 'VARCHAR':
                $length = $field['length'];
                unset($field['length']);
                $field['length']['intchar'] = $length;
                break ;

            case 'FLOAT':
            case 'DOUBLE':
                $length = explode(',', $field['length']);
                unset($field['length']);
                $field['length']['real'] = $length[0];
                $field['precision'] = $length[1];
                break ;
        }
    }
}
