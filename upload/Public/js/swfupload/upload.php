<?php
$updir="./upload/";//上传目录

$upload_file = $_FILES["Filedata"];

writefile('1.txt', serialize($_POST), 'a');

if(move_uploaded_file($upload_file["tmp_name"], $updir.$upload_file['name'])){
	$aaa = "{'filename':'" .$upload_file['name']. "','id':'" . $id . "'}";
	echo $aaa;
}else{
	echo 'upload-error:出错了';
}


//写入文件内容
function writefile($filename, $data, $method = 'wb', $chmod = 1) {
	$return = false;
	if (strpos($filename, '..') !== false) {
		 exit('Write file failed');
	}
	if($fp = @fopen($filename, $method )) {
		@flock($fp, LOCK_EX);
		$return = fwrite($fp, $data);
		fclose($fp);
		$chmod && @chmod($filename,0777);
	}
	return $return;
}

?>