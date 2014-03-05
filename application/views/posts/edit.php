<h1>Edit Post</h1>
<?php
echo $this->html->form("create");echo $this->html->input("id","",array("type" => "hidden","value" => $post["Post"]["id"]));
echo $this->html->input("titulo","Titulo", array("type" => "text", "value" => $post["Post"]["titulo"]));
echo $this->html->input("contenido","Contenido", array("type" => "textarea", "value" => $post["Post"]["contenido"]));
echo $this->html->input("id","",array("type" => "hidden","value" => $post["Post"]["id"]));
echo $this->html->form("submit","Save changes");
echo $this->html->form("end");