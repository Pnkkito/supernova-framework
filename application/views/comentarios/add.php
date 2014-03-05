<h1>Add Comentario</h1>
<?php
echo $this->html->form("create");
echo $this->html->select("post_id","Post", array("options" => $Post , "type" => "text"));
echo $this->html->input("comentario","Comentario", array("type" => "textarea"));
echo $this->html->form("submit","Add new Comentario");
echo $this->html->form("end");