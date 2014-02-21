<?php
class postsController extends AppController {

	function index(){
		$posts = Post::find("all");
		$this->set(compact("posts"));
	}

	function add(){
		if ($this->data){
			Post::save($this->data);
			$this->setMessage("New data saved","success");
			$this->redirect(array("controller" => "posts", "action" => "index"));	
		}
		
	}

	function edit($id = null){
		if ($this->data){
			Post::save($this->data);
			$this->setMessage("Data saved","success");
			$this->redirect(array("controller" => "posts", "action" => "index"));
		}
		if ($id){
			$post = Post::findBy("ID",$id);
			$this->set(compact("post"));
		}else{
			$this->setMessage("Incorrect ID","error");
			$this->redirect(array("controller" => "posts", "action" => "index"));
		}
		
	}

	function delete($id = null){
		if ($id){
			Post::delete($id);
			$this->setMessage("Data deleted","success");
			$this->redirect(array("controller" => "posts", "action" => "index"));		
		}else{
			$this->setMessage("Incorrect ID","error");
			$this->redirect(array("controller" => "posts", "action" => "index"));
		}
	}
}
