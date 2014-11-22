<?php

namespace Home\Logic;

/**
 * InputLogic
 */
class InputLogic extends CommonLogic {
    /**
     * text、password
     */
    const INPUT_SIZE = 20;

    /**
     * textarea
     */
    const INPUT_ROWS = 4;

    /**
     * textarea
     */
    const INPUT_COLS = 68;

    /**
     * editor
     */
    const EDITOR_ROWS = 12;

    /**
     * editor
     */
    const EDITOR_COLS = 84;

    /**
     * file
     */
    const UPLOAD_SIZE = 2;

    /**
     * 按照类型处理合适的表单域大小
     * @param  array $input
     * @return array
     */
    public function genSize(&$input) {
        switch ($input['type']) {
            case 'text':
            case 'password':
                $input['width'] = $input['width']['text_pwd'];
                $input['height'] = 0;
                $this->resetSize($input['width'], self::INPUT_SIZE);
                break ;

            case 'textarea':
                $input['width'] = $input['width']['textarea'];
                $input['height'] = $input['height']['textarea'];
                $this->resetSize($input['width'], self::INPUT_COLS);
                $this->resetSize($input['height'], self::INPUT_ROWS);
                break ;

            case 'editor':
                $input['width'] = $input['width']['editor'];
                $input['height'] = $input['height']['editor'];
                $this->resetSize($input['width'], self::EDITOR_COLS);
                $this->resetSize($input['height'], self::EDITOR_ROWS);
                break ;

            case 'file':
                $input['width'] = $input['width']['upload'];
                $input['height'] = 0;
                $this->resetSize($input['width'], self::UPLOAD_SIZE);
                break ;

            default:
                $input['width'] = 0;
                $input['height'] = 0;
                break ;
        }
    }

    /**
     * 生成表单域对应的html
     * @param  array  $input
     * @param  array  $field 字段信息
     * @return string
     */
    public function genHtml(&$input, $field) {
        $width = $input['width'];
        $height = $input['height'];
        $value = $input['value'];
        $type = $input['type'];
        $remark = $input['remark'];
        $class = 'input';

        if ('file' == $type) {
            $fn = "{$field['name']}";
        } else {
            $fn = "{$field['model']}[{$field['name']}]";
        }

        $html = '';
        if ('text' == $type) {
            $html = genText($fn, $width, $value, $class);
        } else if ('password' == $type) {
            $html = genPassword($fn, $width, $value, $class);
        } else if ('select' == $type) {
            $list = $this->optValueToArray($input['opt_value']);
            $html = genSelect($fn, $list['opt_value'], $list['selected']);
        } else if ('radio' == $type) {
            $list = $this->optValueToArray($input['opt_value']);
            $html = genRadios($fn, $list['opt_value'], $list['selected']);
        } else if ('checkbox' == $type) {
            $list = $this->optValueToArray($input['opt_value'], true);
            $html = genCheckboxs($fn, $list['opt_value'], $list['selected']);
        } else if ('file' == $type) {
            $html = genFile($fn);
        } else if ('textarea' == $type) {
            $html = genTextarea($fn, $value, $width, $height, $remark);
        } else if ('date' == $type) {
            $html = genDate($fn, $value, $class);
        } else if ('relation_select' == $type) {
            $relaOpts = $this->getRelationOpts($field);
            $input['opt_value'] = $this->optArrayToString($relaOpts);
            $html = genSelect($fn, $relaOpts['opt_value']);
        } else if ('editor' == $type) {
            $html = genEditor($fn, empty($value) ? $remark : $value,
                              $width, $height, $input['editor']);
        }

        $input['html'] = $html;
    }

    /**
     * 得到关联可选项数组
     * @param  array $field
     * @return array
     */
    public function getRelationOpts($field) {
        if (is_null($field['relation_model'])
            || is_null($field['relation_field'])
            || is_null($field['relation_value'])) {
            return '';
        }

        $rv = $field['relation_value'];
        $rf = $field['relation_field'];
        $rm = $field['relation_model'];

        // 得到需要关联的模型
        $rm = M('Model')->field('tbl_name')->getById($rm);
        if (empty($rm)) {
            return '';
        }

        // 得到不带前缀的表名
        $tblName = substr($rm['tbl_name'], strlen(C('DB_PREFIX')));
        // 得到对应模型表中的关联字段
        $opts = M($tblName)->field("{$rv},{$rf}")->select();

        $list = array();
        foreach ($opts as $key => $part) {
            $list[$part[$rv]] = $part[$rf];
        }

        return array('opt_value' => $list, 'selected' => 0);
    }

    /**
     * 解析可选值字符为数组
     * @param  string  需要解析的字符串
     * @param  boolean 是否允许多选
     * @return array
     */
    public function optValueToArray($optValue, $mutilSelectd = false) {
        $parts = array();
        $selected = $mutilSelectd ? '' : 0;

        $optValue = str_replace("\r\n", "\n", $optValue);
        $list = array_filter(explode("\n", $optValue));

        foreach ($list as $key => $item) {
            $part = array_filter(explode(':', $item), function($part) {
                return !(empty($part) && 0 !== $part && '0' !== $part);
            });

            if (isset($part[2]) && 'default' == strtolower($part[2])) {
                if ($mutilSelectd) {
                    $selected .= "{$key},";
                } else {
                    $selected = $key;
                }
            }

            $parts[$part[1]] = $part[0];
        }

        return array('opt_value' => $parts, 'selected' => $selected);
    }

    /**
     * 可选值组合成字符串
     * @param  array  $options 可选值数组
     * @return string
     */
    public function optArrayToString(array $options, $mutilSelectd = false) {
        $opts = '';
        $selected = $options['selected'];

        if ($mutilSelectd) {
            $selected = explode(',', $options['selected']);
            $selected = array_filter($selected, function($pos) {
                return !(empty($pos) && 0 !== $pos && '0' !== $pos);
            });
        }

        $i = 0;
        foreach ($options['opt_value'] as $key => $option) {
            $opts .= "{$option}:{$key}";
            if ($mutilSelectd) {
                $opts .= (in_array($i++, $selected)) ? ":default\r\n" : "\r\n";
            } else {
                $opts .= ($i++ == $selected) ? ":default\r\n" : "\r\n";
            }
        }

        return $opts;
    }

    private function resetSize(&$value, $default) {
        if (!isset($value) || '' == $value) {
            $value = $default;
        }
    }
}
