<?php
class comentariosController extends AppController {

	function index(){
		$comentarios = Comentario::find("all");
		$this->set(compact("comentarios"));
	}

	function add(){
		if ($this->data){
			Comentario::save($this->data);
			$this->setMessage("New data saved","success");
			$this->redirect(array("controller" => "comentarios", "action" => "index"));	
		}
		
       $Post = Post::getList();
       $this->set(compact("Post"));

	}

	function edit($id = null){
		if ($this->data){
			Comentario::save($this->data);
			$this->setMessage("Data saved","success");
			$this->redirect(array("controller" => "comentarios", "action" => "index"));
		}
		if ($id){
			$comentario = Comentario::findBy("ID",$id);
			$this->set(compact("comentario"));
		}else{
			$this->setMessage("Incorrect ID","error");
			$this->redirect(array("controller" => "comentarios", "action" => "index"));
		}
		
       $Post = Post::getList();
       $this->set(compact("Post"));

	}

	function delete($id = null){
		if ($id){
			Comentario::delete($id);
			$this->setMessage("Data deleted","success");
			$this->redirect(array("controller" => "comentarios", "action" => "index"));		
		}else{
			$this->setMessage("Incorrect ID","error");
			$this->redirect(array("controller" => "comentarios", "action" => "index"));
		}
	}
}
