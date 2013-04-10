<?php

$outputController = '<?php
class '.strtolower(Inflector::pluralize($modelName)).'Controller extends AppController {

	function index(){
		$'.strtolower(Inflector::pluralize($modelName)).' = $this->'.$modelName.'->find("all");
		$this->set(compact("'.strtolower(Inflector::pluralize($modelName)).'"));
	}

	function add(){
		if ($this->data){
			$this->'.$modelName.'->save($this->data);
			$this->setMessage("New data saved","success");
			$this->redirect(array("controller" => "'.strtolower(Inflector::pluralize($modelName)).'", "action" => "index"));	
		}
	}

	function edit($id = null){
		if ($this->data){
			$this->'.$modelName.'->save($this->data);
			$this->setMessage("Data saved","success");
			$this->redirect(array("controller" => "'.strtolower(Inflector::pluralize($modelName)).'", "action" => "index"));
		}
		if ($id){
			$'.strtolower($modelName).' = $this->'.$modelName.'->findBy("ID",$id);
			$this->set(compact("'.strtolower($modelName).'"));
		}else{
			$this->setMessage("Incorrect ID","error");
			$this->redirect(array("controller" => "'.strtolower(Inflector::pluralize($modelName)).'", "action" => "index"));
		}
	}

	function delete($id = null){
		if ($id){
			$this->'.$modelName.'->delete($id);
			$this->setMessage("Data deleted","success");
			$this->redirect(array("controller" => "'.strtolower(Inflector::pluralize($modelName)).'", "action" => "index"));		
		}else{
			$this->setMessage("Incorrect ID","error");
			$this->redirect(array("controller" => "'.strtolower(Inflector::pluralize($modelName)).'", "action" => "index"));
		}
	}
}
';

echo $outputController;