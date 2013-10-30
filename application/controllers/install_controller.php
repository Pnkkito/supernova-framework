<?php
class installController extends AppController {
	
	//Setting database and models
	function index(){
		$this->layout("admin");
		if ($this->post){
			$SQL = new SQLQuery;
			$host = $this->post['Install']['host'];
			$user = $this->post['Install']['user'];
			$pass = $this->post['Install']['pass'];
			$dbname = $this->post['Install']['dbname'];
			$driver = $this->post['Install']['driver'];
			if ($SQL->connect($host,$user,$pass,$dbname,$driver)){
				
				//Generate database.json file
				$this->set(compact('host','user','pass','dbname','driver'));
				$this->layout("ajax");
				$this->render('templates/databaseOutput','file',ROOT.'/config/database.json');

				//Create build.properties files based on database.json file
				Propel::generate('config');

				//Generate obtect model files
				Propel::generate('model'); // propel-gen om

				//Generate SQL Schema File (schema.sql) from database for backup purposes
				Propel::generate('sql');

				$this->setMessage('Your database conection has saved, now Blackhole your Supernova','success');
				$this->redirect(array('controller' => 'install','action' => 'blackhole'));

			}else{
				$this->setMessage('Please check again your conection parameters');
			}
		}
	}

	//Blackhole MVC maker
	function blackhole(){
		$this->layout("admin");
		
		//Check for connection
		$SQL = new SQLQuery;
		if (!$SQL->connect()){
			$this->setMessage('Seems you have some problems with your database conection, please check','error');
			$this->redirect(array('controller' => 'install','action' => 'index'));
		}

		$tables = $SQL->getTables();
		if ($this->post){
			$table = $this->post['Install']['tablename'];

			if ($table == "Choose your model..."){
				$this->setMessage('Please select your model to Blackhole it','error');
			}else{

				$modelName = $tables[$table];
				$queryModel = ucfirst($modelName).'Peer';
				$Table = $queryModel::getTableMap();
				$Columns = $Table->getColumns();
				$this->set(compact('modelName','Table','Columns'));
				// http://propelorm.org/Propel/cookbook/runtime-introspection.html

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

	function regenerateModels(){
		//Create build.properties files based on database.json file
		Propel::generate('config');

		//Generate obtect model files
		Propel::generate('model'); // propel-gen om

		//Generate SQL Schema File (schema.sql) from database for backup purposes
		Propel::generate('sql');
	}

	// function authExample(){
	// 	$this->Auth = new Auth();
	// 	$hash = $this->Auth->passwordHash('hola mundo');
	// 	$verify = $this->Auth->passwordVerify('holamundo','hola mundo');
	// }

}
