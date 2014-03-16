<?php

// 数据库备份配置
return array(
    // 数据库文件备份的目录路径
    'BACKUP_DIR_PATH' => WEB_ROOT . 'Data/',

    // 数据库文件zip存放目录路径
    'BACKUP_ZIP_DIR_PATH' => WEB_ROOT . 'Data/zip/',

    // 数据库文件备份名称前缀
    'BACKUP_PREFIX' => 'ea_',

    // 数据库备份文件名中的随机数长度
    'BACKUP_FILE_CODE_LENGTH' => 6,

    // sql文件注释头名称
    'BACKUP_DESCRIPTION_NAME' => 'Easy-Admin Backup File.',

    // sql文件注释头url
    'BACKUP_DESCRIPTION_URL' => 'Github: http://github.com/happen-zhang/'
                                . 'easy-admin',

    // 读取sql文件注释的最大字节数
    'BACKUP_DESCRIPTION_LENGTH' => 2000,

    // sql每页条数
    'BACKUP_SQL_LIST_ROWS' => 10000,

    //该值不可太大，否则会导致内存溢出备份、恢复失败，合理大小在512K~10M间，建议5M一卷
    //10M=1024*1024*10=10485760
    //5M=5*1024*1024=5242880
    'SQL_FILE_SIZE' => 5242880, 
);
