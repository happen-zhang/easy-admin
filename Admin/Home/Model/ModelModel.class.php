<?php

namespace Home\Model;

use Think\Model\RelationModel;

/**
 * ModelsModel
 * 数据模型
 */
class ModelModel extends RelationModel {
    // realtions
    protected $_link = array(
        // 一个模型拥有多个字段
        'fields' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'Field',
            'foreign_key' => 'model_id',
        )
    );
}
