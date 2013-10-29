<?php
/**
 * Supernova Framework
 */
/**
 * Behavior handler
 * @package MVC_Model_Behavior
 */
class Behavior {
	
	/**
	 * File
	 * @param	array	$files	File data
	 */
	function file($files){
		
		$fileArr = array();
		
		$modelName = array_keys($files['data']['name']);
		$modelName = $modelName[0];
		
		$kinds = array('name','size','type','tmp_name');
		foreach ($kinds as $kind){
			foreach($files['data'][$kind] as $key => $val){
				$i = 0;
				if (!isset($files['data'][$kind][$modelName]['0'])){
					$noarray = true;
					foreach($val as $fieldKey => $v){
						$fileArr[$i][$key][$kind] = $v;
						$fileArr[$i][$key]['fieldName'] = $fieldKey;
						$i++;	
					}
				}else{
					$noarray = false;
					foreach($files['data'][$kind][$key] as $val){
						foreach ($val as $fieldKey => $v){
							$fileArr[$i][$key][$kind] = $v;
							$fileArr[$i][$key]['fieldName'] = $fieldKey;
							$i++;
						}
					}
				}
			}	
		}
		$robotNames = array();
		
		$namesArr = array();
		foreach($fileArr as $key => $fileModel){
			foreach($fileModel as $keyModel => $file){
				$tamano = $file['size'];
				$fieType = $file['type'];
				$fileName = $file['name'];
				$fieldName = $file['fieldName'];
				$filePrefix = substr(md5(uniqid(rand())),0,6);
				$fileDestiny = $_SERVER["DOCUMENT_ROOT"]."/".Inflector::getBasePath().WEBROOT."/uploads/".$keyModel."/";
				$realName = $filePrefix."_".$fileName;
				$robotName = "uploads/".$keyModel."/".$filePrefix."_".$fileName;
				if (!file_exists($fileDestiny)){
					$create = mkdir($fileDestiny, 0777, TRUE);
				}
				if(!empty($fileName)){
					$max_upload = (int)(ini_get('upload_max_filesize'))*1024*1024;
					$max_post = (int)(ini_get('post_max_size'))*1024*1024;
					$memory_limit = (int)(ini_get('memory_limit'));
					$upload_mb = min($max_upload, $max_post, $memory_limit);
					if ($tamano > $max_upload || $tamano > $max_post || $tamano == 0){
						trigger_error("Can't upload file");
					}
					if (copy($file['tmp_name'],$fileDestiny.$realName)) {
						if ($noarray){
							$namesArr[$fieldName]['file'] = $robotName;
							$namesArr[$fieldName]['type'] = $fieType;
						}else{
							$namesArr[$key][$fieldName]['file'] = $robotName;
							$namesArr[$key][$fieldName]['type'] = $fieType;
						}
					}
				}
			}
		}
		return $namesArr;
	}
}