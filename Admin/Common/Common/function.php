<?php
include('_pre.php');
include('Common/Common/function.php');
include('helper.php');
include('fill_function.php');
include('filter_function.php');

/**
 * 生成验证码
 * @param  array  $config
 * @return
 */
function create_verify_code(array $config) {
    $Verify = new \Think\Verify($config);
    return $Verify->entry();
}

/**
 * 检查验证码
 * @param  string $code
 * @param  int $verify_code_id
 * @return boolean
 */
function check_verify_code($code, $verify_code_id = '') {
    $Verify = new \Think\Verify();
    return $Verify->check($code, $verify_code_id);
}

/**
 * 格式化的文件大小
 * @param  int $bytes
 * @return string
 */
function bytes_format($bytes) {
    // 单位
    $unit = array(' B', ' KB', ' MB',
                  ' GB', ' TB', ' PB',
                  ' EB', ' ZB', ' YB');

    // bytes的对数
    $log_bytes = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $log_bytes), 2) . $unit[$log_bytes];
}

/**
 * 转换Mb单位为Byte大小
 * @return int
 */
function convMb2B($mb) {
    return $mb * 1048576;
}

/**
 * 生成随机码
 * @param  int $length
 * @param  int $type
 * @return string
 */
function rand_code($length, $type) {
    $rand_factor = array("0123456789",
                        "abcdefghijklmnopqrstuvwxyz",
                        "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
                        "~@#$%^&*(){}[]|");

    if (($type < 0 && $type != -1) || $type > 4) {
        $type = 0;
    }

    if (0 == $type) {
        array_pop($rand_factor);
        $rand_src = implode("", $rand_factor);
    } else if (-1 == $type) {
        $rand_src = implode("", $rand_factor);
    } else {
        $rand_src = $rand_factor[$type];
    }

    $code = '';
    $count = strlen($rand_src) - 1;
    for ($i = 0; $i < $length; $i++) {
        $code .= $rand_src[rand(0, $count)];
    }

    return $code;
}

/**
 * 删除目录或者文件
 * @param  string  $path
 * @param  boolean $is_del_dir
 * @return fixed
 */
function del_dir_or_file($path, $is_del_dir = FALSE) {
    $handle = opendir($path);
    if ($handle) {
        // $path为目录路径
        while (false !== ($item = readdir($handle))) {
            // 除去..目录和.目录
            if ($item != '.' && $item != '..') {
                if (is_dir("$path/$item")) {
                    // 递归删除目录
                    del_dir_or_file("$path/$item", $is_del_dir);
                } else {
                    // 删除文件
                    unlink("$path/$item");
                }
            }
        }
        closedir($handle);
        if ($is_del_dir) {
            // 删除目录
            return rmdir($path);
        }
    }else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return false;
        }
    }
}

/**
 * 把文件打包成为zip
 * @param  array $files       需要打包在同一个zip中的文件的路径
 * @param  string $out_dir    zip的文件的输出目录
 * @param  [type] $des_name   zip文件的名称m
 * @return boolean            打包是否成功
 */
function zip($files, $file_path, $out_dir, $des_name) {
    $zip = new ZipArchive;

    // 创建文件夹
    mkdir($out_dir);
    // 打包操作
    $result = $zip->open($out_dir . '/' . $des_name, ZipArchive::CREATE);
    if (true !== $result) {
        return false;
    }

    foreach ($files as $file) {
        // 添加文件到zip包中
        $zip->addFile($file_path . '/' . $file,
                      str_replace('/', '', $file));
    }
    $zip->close();

    return true;
}

/**
 * 解压zip文件
 * @param  string $zip_file 需要解压的zip文件
 * @param  string $out_dir  解压文件的输出目录
 * @return boolean          解压是否成功
 */
function unzip($zip_file, $out_dir) {
    $zip = new ZipArchive();
    if (true !== $zip->open($zip_file)) {
        return false;
    }

    $zip->extractTo($out_dir);
    $zip->close();

    return true;
}

/**
 * 文件上传
 * @param  string $save_path 保存路径
 * @return array
 */
function upload($save_path, $size = -1, $rule = 'uniqid') {
    $upload = new \Org\Util\UploadFile();

    // 文件大小
    $upload->maxSize = $size;
     //设置附件上传目录
    $upload->savePath = WEB_ROOT . $save_path;
    // 上传文件名唯一
    $upload->saveRule = $rule;

    if (!$upload->upload()) {
        //捕获上传异常
        return array('status' => false, 'info' => $upload->getErrorMsg());
    }

    // 得到上传的文件路径
    $info = $upload->getUploadFileInfo();
    foreach ($info as $key => $item) {
        $info[$key]['path'] = $save_path . $item['savename'];
    }

    return array('status' => true, 'info' => $info);
}

/**
 * 检查是否包含特殊字符
 * @param  string  $subject 需要检查的字符串
 * @return boolean          是否包含
 */
function hasSpecialChar($subject) {
    $pattern = "/^(([^\^\.<>%&',;=?$\"':#@!~\]\[{}\\/`\|])*)$/";

    if (preg_match($pattern, $subject)) {
        return false;
    }

    return true;
}

/**
 * 是否整数
 * @param  mixed   $var
 * @return boolean
 */
function isint($var) {
    return (preg_match('/^\d*$/', $var) == 1);
}

/**
 * 是否浮点型
 * @param  mixed   $var 需要检查的值
 * @return boolean
 */
function isdouble($var) {
    return (preg_match('/^[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)$/', $var));
}

/**
 * 检验是否有效日期
 * @param  string  $date    需要检验的日期
 * @param  array   $formats 有效的日期格式
 * @return boolean
 */
function is_valid_date($date, $formats = array("Y-m-d", "Y/m/d")) {
    $unixtime = strtotime($date);
    if (!$unixtime) {
        return false;
    }

    foreach ($formats as $format) {
        if (date($format, $unixtime) == $date) {
            return true;
        }
    }

    return false;
}

/**
 * 得到指定值在数组中的位置，未找到返回false
 * @param  array  $search 被查找的数组
 * @param  mixed  $target 目标值
 * @return mixed
 */
function array_pos(array $search, $target) {
    $i = 0;
    foreach ($search as $item) {
        if ($item == $target) {
            return $i;
        }

        $i += 1;
    }

    return false;
}

/**
 * 只对字符串进行trim
 * @param  mixed $val 需要trim的值
 * @return mixed
 */
function trim_value($val) {
    if (is_string($val)) {
        return trim($val);
    }

    return $val;
}

/**
 * 得到操作系统信息
 * @return string
 */
function get_system() {
    $sys = $_SERVER['HTTP_USER_AGENT'];

    if (stripos($sys, "NT 6.1")) {
        $os = "Windows 7";
    } else if (stripos($sys, "NT 6.0")) {
        $os = "Windows Vista";
    }  else if (stripos($sys, "NT 5.1")) {
        $os = "Windows XP";
    } else if (stripos($sys, "NT 5.2")) {
        $os = "Windows Server 2003";
    } else if (stripos($sys, "NT 5")) {
        $os = "Windows 2000";
    } else if (stripos($sys, "NT 4.9")) {
        $os = "Windows ME";
    } else if (stripos($sys, "NT 4")) {
        $os = "Windows NT 4.0";
    } else if (stripos($sys, "98")) {
        $os = "Windows 98";
    } else if (stripos($sys, "95")) {
        $os = "Windows 95";
    } else if (stripos($sys, "Mac")) {
        $os = "Mac";
    } else if (stripos($sys, "Linux")) {
        $os = "Linux";
    } else if (stripos($sys, "Unix")) {
        $os = "Unix";
    } else if (stripos($sys, "FreeBSD")) {
        $os = "FreeBSD";
    } else if (stripos($sys, "SunOS")) {
        $os = "SunOS";
    } else if (stripos($sys, "BeOS")) {
        $os = "BeOS";
    } else if (stripos($sys, "OS/2")) {
        $os = "OS/2";
    } else if (stripos($sys, "PC")) {
        $os = "Macintosh";
    } else if(stripos($sys, "AIX")) {
        $os = "AIX";
    } else {
        $os = "未知操作系统";
    }

    return $os;
}

/**
 * 发送邮件
 * @param  string $to         收件人邮箱
 * @param  string $name       收件人名称
 * @param  string $subject    邮件主题
 * @param  string $body       邮件正文
 * @return mixed
 */
function smtp_mail($to, $name, $subject = '', $body = '', array $config) {
    Vendor('PHPMailer.PHPMailerAutoload');

    $mail = new PHPMailer();
    // 设置字符集
    $mail->CharSet = $config['SMTP_CHARSET'];
    // 设定使用SMTP服务
    $mail->IsSMTP();
    // html格式内容
    $mail->IsHTML(true);
    // 启用 SMTP 验证功能
    $mail->SMTPAuth = $config['SMTP_AUTH'];
    // SMTP 安全协议
    $mail->SMTPSecure = 'ssl';
    // SMTP 服务器
    $mail->Host = $config['SMTP_HOST'];
    // SMTP服务器的端口号
    $mail->Port = $config['SMTP_PORT'];
    // SMTP服务器用户名
    $mail->Username = $config['SMTP_USER_NAME'];
    // SMTP服务器密码
    $mail->Password = $config['SMTP_PASSWORD'];
    // 设置发送者信息
    $mail->SetFrom($config['MAIL_FROM'], $config['SENDER_NAME']);
    // 设置邮件回复者信息
    $mail->AddReplyTo($config['MAIL_REPLY'], $config['REPLYER_NAME']);
    // 设置邮件主题
    $mail->Subject = $subject;
    // 设置邮件内容
    $mail->MsgHTML($body);
    // 兼容不支持html的邮件
    $mail->AltBody = 'This is the body in plain text';
    //
    $mail->AddAddress($to, $name);

    return $mail->Send() ? true : $mail->ErrorInfo;
}
