# Easy-Admin 通用后台信息管理系统 #

[Easy-Admin](https://github.com/happen-zhang/easy-admin)是一个通用的后台信息管理系统。

## 系统说明 ##

Easy-Admin就如其名，它能够为你快速搭建一个方便的后台信息管理系统，并且提供简洁易用的管理界面。大部分CMS系统往往功能都是比较庞大繁杂的，不管从学习使用或者是进行二次开发都是成本比较高的。Easy-Admin不是一个CMS，它仅仅帮你创建一个方便可用的后台信息管理，并集成一些常用的操作。简而言之，Easy-Admin能够帮你轻松搞定信息的CURD、权限管理（多管理员）和数据文件管理。手头有企业站小伙伴们可以试试看，能帮到你就是这个项目的价值所在。

> 查看截图：[点这](http://happen-zhang.github.io/2014/05/15/easy-admin-intro/)

## 安装 ##

### 环境要求 ###

> 请确保当前系统的PHP版本大于5.3。

### 克隆项目 ###

```
git clone https://github.com/happen-zhang/easy-admin.git
```

### 访问安装文件 ###

```
http://domain/easy-admin/install.php
```

## 文件结构 ##

当前项目文件提供的是安装时所需最小要求，安装前后的目录结果会有所变化。安装后系统自动将删除`Install`目录和`install.php`文件，同时生成`Data`，`Common`，`Cache`三个目录。

下面是安装前的目录结构：

```
Easy-Admin
├── Admin
│   ├── Common
│   │   ├── Common
│   │   └── Conf
│   └── Home
│       ├── Controller
│       ├── Logic
│       ├── Model
│       ├── Service
│       └── View
├── Install
│   ├── Common
│   │   ├── Common
│   │   └── Conf
│   ├── Data
│   └── Home
│       ├── Controller
│       └── View
├── Public
│   ├── images
│   │   ├── admin
│   │   └── install
│   ├── javascripts
│   │   ├── admin
│   │   └── install
│   ├── Min
│   │   ├── builder
│   │   └── lib
│   ├── stylesheets
│   │   ├── admin
│   │   └── install
│   └── uploads
├── ThinkPHP
├── LICENSE
├── README.md
├── admin.php
└── install.php
```

## 自定义 ##

### 支持数据类型 ###

```
字符型：char, varchar

整型：tinyint, int, smallint, bigint

浮点型：float, double

文本型：text, mediumtext, longtext
```

### 支持表单域类型 ###

```
text：文本域

password：密码域

select：下拉框

radio：单选框

checkbox：复选框

textarea：多行文本域

file：文件上传，返回的值是文件存放的位置

date：日期控件

editor：编辑器，KingEditor全功能和简介功能

relationlink：可关联其他模型的下拉框
```

### 主菜单 ###

菜单配置在`Admin/Comom/Conf/menu_config.php`文件中。

```
// File: Admin/Comom/Conf/menu_config.php

'Posts' => array(
    'name' => '文章管理',
    'target' => 'Posts/index',
    // 'mapping' => 'test',
    'sub_menu' => array(
        array('item' => array('Posts/index' => '文章列表')),
        array('item' => array('Posts/add') => '添加文章')),
        array('item' => array('Posts/edit') => '修改文章')),
        array('item' => array('Posts/delete') => '删除文章'))
    )
)

'Posts'：菜单对应的模块名称（Controller的名字）

'name'：主菜单中显示出来的名字

'target'：点击主菜单后跳转到的操作

'sub_menu'：主菜单下的子菜单，即左侧的菜单

'mapping'：把该子菜单映射到某个主菜单下
```

### 过滤函数 ###

你可以选择过滤函数来过滤表单提交后某字段的值。比如，我们不希望**文章标题**中出现HTML效果，我们需要对它进行转义，那么我们就可以注册自定过滤函数来实现这个需求。

我们在`Common/Common/`目录下创建`filter_function.php`文件：

```
// File: Common/Common/filter_function.php

// 自定义的过滤函数
function my_html_filter($val) {
    return htmlspecialchars($val);
}

// 注册函数
// registry_filter方法的参数必须是一个数组
registry_filter(array(
    // 函数名
    'my_html_filter'
));

// 不管是自定义还是php自带的函数，只要函数定义过了都可以注册
// registry_filter(array(
//    'htmlspecialchars'
// ));
```

经过上面的操作后我们就可以在字段定义中选择使用这个自定义函数了。

### 填充函数 ###

我们有时希望某些字段值能自动填充，那么我们就可以通过注册填充函数来实现。比如，我们需要为每篇生成一个随机的uuid。

```
// File: Common/Common/fill_function.php

function uuid() {
    $uuid = '';
    // some logic code here
    return $uuid;
}

registry_fill(array(
    // 函数名
    'uuid'
));
```

自定义填充函数和自定义过滤函数是一样的。

> 过滤函数必须要有一个参数和一个返回值，填充函数需要一个返回值。

> 内置filter：sql_injection、strip_sql_injection、filter_special_chars

> 内置fill：uuid、datetime

## 可配置项 ##

系统提供较多的可配置项，当然你也可以完全不管，功能依然能够正常使用。

### 安全配置 ###

```
// File: Admin/Comom/Conf/security_config.php

// 表单令牌
'TOKEN_ON' => false
'TOKEN_NAME' => '__hash__'
'TOKEN_TYPE' => 'md5'
'TOKEN_RESET' => true

// 认证token
'AUTH_TOKEN' => 'eaadmin'
// 登录超时
'LOGIN_TIMEOUT' => 3600

// 不用认证登录的模块
'NOT_LOGIN_MODULES' => 'Public'

// 开启权限认证
'USER_AUTH_ON' => true

// 登录认证模式
'USER_AUTH_TYPE' => 1

// 认证识别号
'USER_AUTH_KEY' => 'mineaad'

// 超级管理员认证号
'ADMIN_AUTH_KEY' => 'eaadminae'

// 游客识别号
'GUEST_AUTH_ID' => 'guest'

// 无需认证模块
'NOT_AUTH_MODULE' => 'Public'

// 需要认证模块
'REQUIRE_AUTH_MODULE' => ''

// 认证网关
'USER_AUTH_GATEWAY' => 'Public/index'

// 关闭游客授权访问
'GUEST_AUTH_ON' => false

// 管理员模型
'USER_AUTH_MODEL' => 'Admin'
```

### 邮箱配置 ###

```
// File: Admin/Comom/Conf/mail_config.php

// SMTP服务器
'SMTP_HOST' => 'smtp.example.com'

// SMTP认证
'SMTP_AUTH' => true

// SMTP端口
'SMTP_PORT' => 465

// SMTP服务器用户名
'SMTP_USER_NAME' => 'smtpservername'

// SMTP服务器密码
'SMTP_PASSWORD' => 'smtpserverpwd'

// 发送邮件的邮箱地址
'MAIL_FROM' => 'email@example.com'

// 发送邮件的发送者名称
'SENDER_NAME' => 'ea-admin'

// 回复者邮件
'MAIL_REPLY' => 'email@example.com'

// 回复者名称
'REPLYER_NAME' => 'youname'

// 字符集
'SMTP_CHARSET' =>'UTF-8'

// 邮件内容替换，?为占位符
'MAIL_BODY' => '在浏览器中运行下面的链接进行重置密码操作：<br/><a href="?">?</a>'
```

### 数据备份 ###

```
// File: Admin/Comom/Conf/backup_config.php

// 数据库文件备份的目录路径
'BACKUP_DIR_PATH' => WEB_ROOT . 'Data/'

// 数据库文件zip存放目录路径
'BACKUP_ZIP_DIR_PATH' => WEB_ROOT . 'Data/zip/'

// 数据库文件备份名称前缀
'BACKUP_PREFIX' => 'ea_'

// 数据库备份文件名中的随机数长度
'BACKUP_FILE_CODE_LENGTH' => 6

// sql文件注释头名称
'BACKUP_DESCRIPTION_NAME' => 'Easy-Admin Backup File.'

// sql文件注释头url
'BACKUP_DESCRIPTION_URL' => 'Github: http://github.com/happen-zhang/easy-admin'

// 读取sql文件注释的最大字节数
'BACKUP_DESCRIPTION_LENGTH' => 2000

// sql每页条数
'BACKUP_SQL_LIST_ROWS' => 10000

// sql文件分卷大小
'SQL_FILE_SIZE' => 5242880
```

## 约定 ##

### 方法名 ###

```
add：数据表单添加页面

create：对提交的表单数据处理后存到数据库

edit：数据表单编辑页面

update：对提交的表单数据处理后更新到数据库
```

```
方法名：helloWorld()

类中的变量名（包括方法中的变量）：$helloWorld = 'hi'

函数：hello_world()

类外部的变量名：$hello_world = 'hi'

键值: beginer['hello_world'] = 'hi'

表单域名：<input type='text' name='goods[some_detail]' />
```

## 扩展库说明 ##

本系统的开发没有修改过ThinkPHP中的核心系框架中的内容，如果需要，你可以尝试着对ThinkPHP版本的升级。

下面是引用到的PHP第三方库：

* Min：Public/Min
* PHPMailer：ThinkPHP/Library/Vendor/PHPMailer：ThinkPHP

下面是ThinkPHP工具类，均放在 ThinkPHP/Library/Org/Util/ 目录下：

* Page.class.php：分页类 __有改动__
* Rbac.class.php：角色权限管理
* UploadFile.class.php：文件上传类 __有改动__
* Category.class.php：无限分级类

## 补充说明 ##

1. 本系统的**页面**来源于 @leohdr ，在此感谢 @leohdr 兄的分享。
2. 本系统的代码都是我一个人所写，我十分愿意分享给大家。由于个人精力有限，系统可能还存在尚未发现的bug，如果你在使用系统的过程发现bug，可以issuse给我，谢谢。

## License ##

(The MIT License)

Copyright (c) 2014 happen-zhang <zhanghaipeng404@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
'Software'), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
