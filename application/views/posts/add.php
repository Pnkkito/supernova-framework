<h1>Add Post</h1>
<?php
echo $this->html->form("create");
echo $this->html->input("titulo","Titulo", array("type" => "text"));
echo $this->html->input("contenido","Contenido", array("type" => "textarea"));

echo $this->html->form("submit","Add new Post");
echo $this->html->form("end");