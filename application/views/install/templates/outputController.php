<?php

$tableName = $Table->getPhpName();

$outputController = '<?php
class '.$tableName.'Controller extends AppController {

	function index(){
		$'.$tableName.' = '.$tableName.'Query::create()->find();
		$this->set(compact("'.$tableName.'"));
	}

	function add(){
		if ($this->post){
			$'.$tableName.' = new '.$tableName.'();
			$'.$tableName.'->fromArray($this->post);
			$'.$tableName.'->save();
			$this->setMessage("New data saved","success");
			$this->redirect(array("controller" => "'.$tableName.'", "action" => "index"));	
		}
	}

	function edit($id = null){
		if ($this->post){
			$'.$tableName.' = '.$tableName.'Query::create()->findPk($id);
			$'.$tableName.'->fromArray($this->post);
			$'.$tableName.'->save();
			$this->setMessage("Data saved","success");
			$this->redirect(array("controller" => "'.$tableName.'", "action" => "index"));
		}
		if ($id){
			$'.$tableName.' = $'.$tableName.'Query::create()->findPk($id);
			$this->set(compact("'.$tableName.'"));
		}else{
			$this->setMessage("Incorrect ID","error");
			$this->redirect(array("controller" => "'.$tableName.'", "action" => "index"));
		}
	}

	function delete($id = null){
		if ($id){
			$'.$modelName.'Query::create()->findPk($id)->delete();
			$this->setMessage("Data deleted","success");
			$this->redirect(array("controller" => "'.$tableName.'", "action" => "index"));		
		}else{
			$this->setMessage("Incorrect ID","error");
			$this->redirect(array("controller" => "'.$tableName.'", "action" => "index"));
		}
	}
}
';

echo $outputController;