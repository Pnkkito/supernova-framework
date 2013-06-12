<?php
class installController extends AppController {
	
	//Internal functions
	function parseFiles(){
		switch ($fileName){
			case "database.json": break;
			case "schema.json":
				
			break;
		}
	}

	//Blackhole2
	function blackhole2(){
		$user = $this->Install->find('all');
	}
	
	//Configure database first
	function index(){
		$this->layout("default");
		if ($this->data){
			$SQL = new SQLQuery;
			$host = $this->data['Install']['host'];
			$user = $this->data['Install']['user'];
			$pass = $this->data['Install']['pass'];
			$dbname = $this->data['Install']['dbname'];
			$driver = $this->data['Install']['driver'];
			$prefix = $this->data['Install']['prefix'];
			if ($SQL->connect($host,$user,$pass,$dbname,$driver)){
				//Save params in database.ini
				$this->set(compact('host','user','pass','dbname','driver','prefix'));
				$this->layout("ajax");
				$this->render('templates/databaseOutput','file',ROOT.'/config/database.ini');
				$this->setMessage('Your database conection has saved, now Blackhole your Supernova','success');
				$this->redirect(array('controller' => 'install','action' => 'blackhole'));
			}else{
				$this->setMessage('Please check again your conection parameters');
			}
		}
	}

	//Blackhole MVC maker
	function blackhole(){
		$this->layout("default");
		$SQL = new SQLQuery;
		if (!$SQL->connect()){
			$this->setMessage('Seems you have some problems with your database conection, please check','error');
			$this->redirect(array('controller' => 'install','action' => 'index'));
		}

		$tables = $SQL->getTables();
		$this->Install->_tables = $tables;

		if ($this->data){
			$table = $this->data['Install']['tablename'];
			if ($table == "Choose your model..."){
				$this->setMessage('Please select your table to Blackhole it','error');
			}else{
				//Get table prefix and index
				$prefix = substr($table,0,2);
				$index = substr($table,2,2);

				//Set default ForeingKey
				$defaultPK = "ID";

				//Set default DisplayField
				$defaultDisplayField = "ID";

				//Get model name
				$modelName = $tables[$table];

				//Get fields for model
				$fields = $SQL->getFields($table);
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

				//Generate new link into the sidebar
				$newLink = "\n"."<!-- ".$modelName." -->"."\n";
				$newLink.= '<li><?=$this->html->link("<i class=\'icon-align-justify\'></i> '.$modelName.'",array("controller" => "'.Inflector::pluralize(Inflector::camel_to_under($modelName)).'", "action" => "index"));?></li>'."\n";
				file_put_contents(ROOT.'/application/views/elements/sidebar.php',$newLink,FILE_APPEND);

				$this->setMessage('The Blackhole has received your model, now all your files has been created successfully','success');
			}
		}
		$this->set(compact('tables'));
	}
	
	function ajaxRequest(){
		$this->layout('ajax');
		if ($this->data){ // format in ajax request -> { data : { action:actionName, key:var, key2:var2, etc... } }
			switch ($this->data['action']){
				case 'getRelatedModels':
					//New functions always should go inside the model class
					$relations = $this->Install->searchForRelations($this->data['table']);
					$this->set(compact('relations'));
				break;
			}
		}
	}

}
