<?php
class pagesController extends AppController {
	
	// function passwordtest(){
	// 	$this->Auth = new Auth();
	// 	$hash = $this->Auth->passwordHash('hola mundo');
	// 	$verify = $this->Auth->passwordVerify('holamundo','hola mundo');
	// }

	function index(){
		
	}

	function install(){
		$this->layout("management");
		$tables = SQLQuery::getTables();
		// sort($tables);
		
		if ($this->data){
			$table = $this->data['Page']['tablename'];
			$prefix = substr($table,0,2);
			$index = substr($table,2,2);
			$defaultPK = "ID";
			$defaultDisplayField = "ID";
			$modelName = $tables[$table];
			// $modelName = Inflector::singularize(Inflector::under_to_camel(strtolower(substr($table,5))));
			
			//Building Model File
			$outputModel = '<?php

class '.$modelName.' extends Model {

	var $index = "'.$index.'";
	var $primaryKey = "'.$defaultPK.'";
	var $displayField = "'.$defaultDisplayField.'";

}
';

			$fileName = strtolower($modelName).".php";
			$dirName = ROOT.'/application/models/';
			chdir(ROOT);
			chdir('./application/models');

			file_put_contents($fileName,$outputModel);



			//Building Controller File
			$outputController = '<?php
class '.strtolower(Inflector::pluralize($modelName)).'Controller extends AppController {

	function index(){
		$'.strtolower(Inflector::pluralize($modelName)).' = $this->'.$modelName.'->find("all");
		$this->set(compact("'.strtolower(Inflector::pluralize($modelName)).'"));
	}

	function add(){
		if ($this->data){
			$this->'.$modelName.'->save($this->data);
			$this->setMessage("New data saved");
			$this->redirect(array("controller" => "'.strtolower(Inflector::pluralize($modelName)).'", "action" => "index"));	
		}
	}

	function edit($id = null){
		if ($id){
			$'.strtolower($modelName).' = $this->'.$modelName.'->findBy("ID",$id);
			$this->set(compact("'.strtolower($modelName).'"));
		}
		if ($this->data){
			$this->'.$modelName.'->save($this->data);
			$this->setMessage("Data saved");
			$this->redirect(array("controller" => "'.strtolower(Inflector::pluralize($modelName)).'", "action" => "index"));
		}
	}

	function delete($id = null){
		if ($id){
			$this->'.$modelName.'->delete($id);
			$this->setMessage("Data deleted");
			$this->redirect(array("controller" => "'.strtolower(Inflector::pluralize($modelName)).'", "action" => "index"));		
		}
	}
}
';

			$fileName = Inflector::pluralize(Inflector::camel_to_under($modelName))."_controller.php";
			$dirName = ROOT.'/application/controllers/';
			chdir(ROOT);
			chdir('./application/controllers');
			file_put_contents($fileName,$outputController);

			//Building Views for Model
			$fields = SQLQuery::getFields($table);
			$fieldsUnparsed = Inflector::unparseFields($fields,$modelName);
			
			$indexOutputFile = '<h1>'.$modelName.' List</h1>
<?php
echo $this->html->table($'.strtolower(Inflector::pluralize($modelName)).', array("actions" => true));
echo "<div class=\'buttons\'>";
echo $this->html->link("Add '.strtolower($modelName).'",array("controller" => "'.strtolower(Inflector::pluralize($modelName)).'","action" => "add"));
echo "</div>";';


			$editOutputFile = '<h1>Edit '.Inflector::singularize($modelName).'</h1>
<?php
echo $this->html->form("create");';
			foreach ($fieldsUnparsed as $eachfield => $typefield){
				if (!empty($eachfield)){
					if ($eachfield == $defaultPK){
						$editOutputFile.='echo $this->html->input("'.$defaultPK.'","",array("type" => "hidden","value" => $'.strtolower($modelName).'["'.$modelName.'"]["'.$defaultPK.'"]));';
					}else{
						$editOutputFile.='echo $this->html->input("'.$eachfield.'","'.Inflector::under_to_camel(strtolower($eachfield)).'", array("value" => $'.strtolower($modelName).'["'.$modelName.'"]["'.$eachfield.'"]));';
					}
				}
			}
$editOutputFile.='$this->html->form("submit","Save changes");
echo $this->html->form("end");';


			$addOutputFile = '<h1>Add '.Inflector::singularize($modelName).'</h1>
<?php
echo $this->html->form("create");';
			foreach ($fieldsUnparsed as $eachfield => $typefield){
				if (!empty($eachfield)){
					if ($eachfield != $defaultPK){
						$addOutputFile.='echo $this->html->input("'.$eachfield.'","'.Inflector::under_to_camel(strtolower($eachfield)).'");';
					}
				}
			}

$addOutputFile.='echo $this->html->form("submit","Add new '.Inflector::singularize($modelName).'");
echo $this->html->form("end");';

			$dirName = ROOT.'/application/views/'.Inflector::pluralize(Inflector::camel_to_under($modelName));
			chdir(ROOT);
			chdir('./application/views');
			mkdir(Inflector::pluralize(Inflector::camel_to_under($modelName)), 0777, true);
			chdir('./'.Inflector::pluralize(Inflector::camel_to_under($modelName)));
			file_put_contents('index.php',$indexOutputFile);
			file_put_contents('edit.php',$editOutputFile);
			file_put_contents('add.php',$addOutputFile);

			$this->setMessage('All BlackHole files created successfully');
		}
		$this->set(compact('tables'));
	}
	
}
?>