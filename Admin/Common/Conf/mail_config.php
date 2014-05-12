<?php

return array(
    // SMTP服务器
    'SMTP_HOST' => 'smtp.qq.com',
    // SMTP认证
    'SMTP_AUTH' => true,
    // SMTP端口
    'SMTP_PORT' => 465,
    // SMTP服务器用户名
    'SMTP_USER_NAME' => '543438708@qq.com',
    // SMTP服务器密码
    'SMTP_PASSWORD' => 'zhpqq543438708.',
    // 发送邮件的邮箱地址
    'MAIL_FROM' => '543438708@qq.com',
    // 发送邮件的发送者名称
    'SENDER_NAME' => 'ea-admin',
    // 回复者邮件
    'MAIL_REPLY' => '543438708@qq.com',
    // 回复者名称
    'REPLYER_NAME' => 'happen',
    // 字符集
    'SMTP_CHARSET' =>'UTF-8',
    // 邮件内容替换，?为占位符
    'MAIL_BODY' => '在浏览器中运行下面的链接进行重置密码操作：<br/><a href="?">?</a>'
);
