<?php
class installController extends AppController {
	
	//Setting database and models
	function index(){
		$this->layout("admin");
		if ($this->post){
			$host = $this->post['Install']['host'];
			$user = $this->post['Install']['user'];
			$pass = $this->post['Install']['pass'];
			$dbname = $this->post['Install']['dbname'];
			$driver = $this->post['Install']['driver'];
			if (SQLQuery::connect($host,$user,$pass,$dbname,$driver)){
				
				//Generate database.json file
				$this->set(compact('host','user','pass','dbname','driver'));
				$this->layout("ajax");
				$this->render('templates/databaseOutput','file',ROOT.'/config/database.json');

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
		if (!SQLQuery::connect()){
			$this->setMessage('Seems you have some problems with your database conection, please check','error');
			$this->redirect(array('controller' => 'install','action' => 'index'));
		}

        $tables = SQLQuery::getTables();
		if ($this->data){
			$table = $this->data['Install']['tablename'];
			if ($table == "Choose your model..."){
				$this->setMessage('Please select your model to Blackhole it','error');
			}else{
			    // Store schema in cache
			    $fields = SQLQuery::storeTableInCache($table);
			    $foreingKeys = SQLQuery::storeTableRelationsInCache($table);
			    
			    // Set default ForeingKey
				$defaultPK = SQLQuery::getTablePrimaryKey($table);
				$defaultDisplayField = SQLQuery::getTableDisplayField($table);
				
				//Get model name
				$modelName = $tables[$table];
            
				$this->set(compact('modelName','table','fields','foreingKeys','defaultPK','defaultDisplayField'));
                
                umask(0);
                
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
				    if ( !is_writable(getcwd()) )
    				{
    				    warning ("Can't create directory <strong>".$modelDirName.'</strong> into: '.getcwd().'. Permission problems perhaps?');
    				    include (ERRORS_PATH . '500.php');
    			        die();
    				}else{
					    mkdir($modelDirName, 0777, true);
    				}
				}
				$this->render("templates/indexOutputFile", "file", $dirName."/index.php");
				$this->render("templates/editOutputFile", "file", $dirName."/edit.php");
				$this->render("templates/addOutputFile", "file", $dirName."/add.php");

				//Generate new link into the sidebar
				$newLink = "\n"."<!-- ".$modelName." -->"."\n";
				$newLink.= '<li><?=$this->html->link("<i class=\'icon-align-justify\'></i> '.$modelName.'",array("controller" => "'.Inflector::pluralize(Inflector::camel_to_under($modelName)).'", "action" => "index"));?></li>'."\n";
				if ( is_writable( ROOT.'/application/views/elements' ) && is_writable( ROOT.'/application/views/elements/sidebar.php' )){
				    file_put_contents(ROOT.'/application/views/elements/sidebar.php',$newLink,FILE_APPEND);
				}else{
				    warning ("Can't create/modify file <strong>sidebar.php</strong> into: <strong>/application/views/elements</strong>. Permission problems perhaps?");
				    include (ERRORS_PATH . '500.php');
			        die();
				}
				$this->setMessage('The Blackhole has received your model, now all your files has been created successfully','success');
			}
		}
		$this->set(compact('tables'));
	}

	// function authExample(){
	// 	$this->Auth = new Auth();
	// 	$hash = $this->Auth->passwordHash('hola mundo');
	// 	$verify = $this->Auth->passwordVerify('holamundo','hola mundo');
	// }

}
