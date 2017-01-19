<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   文件处理相关函数($$)*/

!defined('Q_PATH') && exit;

class File_Extend{

	static public function fileModeInfo($sFilePath){
		if(!file_exists($sFilePath)){// 如果不存在，则不可读、不可写、不可改
			return false;
		}

		$nMark=0;
		if(strtoupper(substr(PHP_OS,0,3))=='WIN'){
			$sTestFile=$sFilePath.'/test.txt';// 测试文件
			if(is_dir($sFilePath)){// 如果是目录
				$sDir=@opendir($sFilePath);// 检查目录是否可读
				if($sDir===false){
					return $nMark;//如果目录打开失败，直接返回目录不可修改、不可写、不可读
				}

				if(@readdir($sDir)!==false){
					$nMark^=1; //目录可读 001，目录不可读 000
				}
				@closedir($sDir);
				
				$hFp=@fopen($sTestFile,'wb');// 检查目录是否可写/
				if($hFp===false){
					return $nMark; //如果目录中的文件创建失败，返回不可写。
				}

				if(@fwrite($hFp,'access test!')!==false){
					$nMark^=2; //目录可写可读011，目录可写不可读 010
				}
				@fclose($hFp);
				@unlink($sTestFile);

				$hFp=@fopen($sTestFile,'ab+');// 检查目录是否可修改
				if($hFp===false){
					return $nMark;
				}

				if(@fwrite($hFp,"modify test.\r\n")!==false){
					$nMark^=4;
				}
				@fclose($hFp);

				if(@rename($sTestFile, $sTestFile)!==false){// 检查目录下是否有执行rename()函数的权限
					$nMark^=8;
				}
				@unlink($sTestFile);
			}elseif(is_file($sFilePath)){// 如果是文件
				$hFp=@fopen($sFilePath,'rb');// 以读方式打开
				if($hFp){
					$nMark^=1; //可读 001
				}
				@fclose($hFp);

				$hFp=@fopen($sFilePath,'ab+');// 试着修改文件
				if($hFp && @fwrite($hFp,'')!==false){
					$nMark^=6; //可修改可写可读 111，不可修改可写可读011...
				}
				@fclose($hFp);

				if(@rename($sTestFile,$sTestFile)!==false){// 检查目录下是否有执行rename()函数的权限
					$nMark^=8;
				}
			}
		}else{
			if(@is_readable($sFilePath)){
				$nMark^=1;
			}

			if(@is_writable($sFilePath)){
				$nMark^=14;
			}
		}

		return $nMark;
	}

}
