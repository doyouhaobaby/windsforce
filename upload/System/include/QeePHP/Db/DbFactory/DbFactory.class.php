<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   数据库工厂类，用于生成数据库相关对象 < 抽象类 >($$)*/

!defined('Q_PATH') && exit;

abstract class DbFactory{

	abstract public function createConnect();

	abstract public function createRecordSet(DbConnect $oConnect);

}
