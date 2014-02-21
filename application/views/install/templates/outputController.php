<?php

$foreingKeyConsult='';
if (!empty($foreingKeys)){
    foreach ($foreingKeys as $FKModel => $FK){
        $foreingKeyConsult.="\n".'       $'.$FKModel.' = '.$FKModel.'::getList();'."\n";
    }
    $foreingKeyConsult.='       $this->set(compact("'.implode(',',array_keys($foreingKeys)).'"));'."\n";
}

$outputController = '<?php
class '.strtolower(Inflector::pluralize($modelName)).'Controller extends AppController {

	function index(){
		$'.strtolower(Inflector::pluralize($modelName)).' = '.$modelName.'::find("all");
		$this->set(compact("'.strtolower(Inflector::pluralize($modelName)).'"));
	}

	function add(){
		if ($this->data){
			'.$modelName.'::save($this->data);
			$this->setMessage("New data saved","success");
			$this->redirect(array("controller" => "'.strtolower(Inflector::pluralize($modelName)).'", "action" => "index"));	
		}
		'.$foreingKeyConsult.'
	}

	function edit($id = null){
		if ($this->data){
			'.$modelName.'::save($this->data);
			$this->setMessage("Data saved","success");
			$this->redirect(array("controller" => "'.strtolower(Inflector::pluralize($modelName)).'", "action" => "index"));
		}
		if ($id){
			$'.strtolower($modelName).' = '.$modelName.'::findBy("ID",$id);
			$this->set(compact("'.strtolower($modelName).'"));
		}else{
			$this->setMessage("Incorrect ID","error");
			$this->redirect(array("controller" => "'.strtolower(Inflector::pluralize($modelName)).'", "action" => "index"));
		}
		'.$foreingKeyConsult.'
	}

	function delete($id = null){
		if ($id){
			'.$modelName.'::delete($id);
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