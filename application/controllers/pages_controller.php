<?php
class pagesController extends AppController {
	
	// function authExample(){
	// 	$this->Auth = new Auth();
	// 	$hash = $this->Auth->passwordHash('hola mundo');
	// 	$verify = $this->Auth->passwordVerify('holamundo','hola mundo');
	// }

	function install(){
		$this->layout("management");
		$conection = new SQLQuery;
		$tables = $conection->getTables();

		if ($this->data){
			//Setting vars
			$table = $this->data['Page']['tablename'];
			$prefix = substr($table,0,2);
			$index = substr($table,2,2);
			$defaultPK = "ID";
			$defaultDisplayField = "ID";
			$modelName = $tables[$table];
			$fields = $conection->getFields($table);
			$fieldsUnparsed = Inflector::unparseFields($fields,$modelName);
			$this->set(compact('modelName','index','defaultPK','defaultDisplayField','table','prefix','fields','fieldsUnparsed'));
			
			//Build Model file
			$dirName = ROOT.'/application/models/';
			$fileName = strtolower($modelName).".php";
			$this->render("templates/outputModel", "file", $dirName.$fileName);
			
			//Build Controller file
			$fileName = Inflector::pluralize(Inflector::camel_to_under($modelName))."_controller.php";
			$dirName = ROOT.'/application/controllers/';
			$this->render("templates/outputController", "file", $dirName.$fileName);
			
			//Build Views files
			$dirName = ROOT.'/application/views/'.Inflector::pluralize(Inflector::camel_to_under($modelName));
			chdir(ROOT);
			chdir('./application/views');
			$modelDirName = Inflector::pluralize(Inflector::camel_to_under($modelName));
			if (!file_exists($modelDirName)){
				mkdir($modelDirName, 0777, true);
			}
			$this->render("templates/indexOutputFile", "file", $dirName."/index.php");
			$this->render("templates/editOutputFile", "file", $dirName."/edit.php");
			$this->render("templates/addOutputFile", "file", $dirName."/add.php");
			$newLink = "\n"."<!-- ".$modelName." -->"."\n";
			$newLink.= '<li><?=$this->html->link("<i class=\'icon-align-justify\'></i> '.$modelName.'",array("controller" => "'.Inflector::pluralize(Inflector::camel_to_under($modelName)).'", "action" => "index"));?></li>'."\n";
			file_put_contents(ROOT.'/application/views/elements/sidebar.php',$newLink,FILE_APPEND);

			$this->setMessage('All BlackHole files created successfully','success');
		}
		$this->set(compact('tables'));
	}
	
}
