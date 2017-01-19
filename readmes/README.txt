/**
 * WindsForce APP Framework (风动社区)
 *
 * @copyright  Copyright (C)WindsForce TEAM Since 2012.03.17.
 * @license    This is NOT a freeware
 */

【WindsForce 安装说明】
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

特别说明: (用户必读!)

  + 您使用本社区前,请详细阅读用户授权文档 LICENSE.txt.切记!在您认为可以满足授权文档
    中列出的要求时,方可使用 WindsForce,否则,请不要将 WindsForce 安装到您的网络上.

  + 如果您不是从WindsForce Team 网站
    http://windsforce.114.ms. 上下载的本程序,很可能获得的不是最新版本.所以安装之前请
    查询上述网址,使用最新的 WindsForce 版本安装.

当前版本: (版本详细升级情况,请查看 Changelog.txt)

  + WindsForce 2.0


  + 确认您的服务器 PHP 版本最低版本为 5.0,Mysql 数据库最低版本为5.0


我们的联系的方式:

  + homepage http://windsforce.114.ms

  + email 635750556@qq.com

  + qq 635750556

【WindsForce 安装教程】
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

在线网址（http://www.114.ms/app/group/topic/181.html ）

---- 将 WindsForce-2.0 压缩包解压至一个空文件夹.

---- 上传 upload 中所有文件到服务器.

---- 上传后,如果你是Linux 主机,那么还需要设定一些目录或者文件的权限为0777,具体根据安装程序的提示来做.
      return array(
          '/~@~/*',
          '/~@~/Config.inc.php',
          '/user/*',
          '/user/attachment/*',
          '/user/avatar/*',
          '/user/database/*',
          '/user/lock/*',
      );

---- 在浏览器中访问 /index.php，WindsForce 会自动建立数据库表。程序会自动检测你是否连接上数据库，如果
      一直提示错误，请仔细检查你填写的数据库信息是否正确。要是依然错误，请前往 WindsForce 官方论坛寻求
      解决方法.

---- 安装程序执行结束后，程序将自动跳转到首页.

【WindsForce 升级教程】
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

升级程序未提供，后面提供转换脚本。

5：升级结束，祝你使用愉快！

@---------------
2014-10-07 By 小牛哥
