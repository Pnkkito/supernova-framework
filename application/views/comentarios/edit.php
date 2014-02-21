<h1>Edit Comentario</h1>
<?php
echo $this->html->form("create");echo $this->html->input("id","",array("type" => "hidden","value" => $comentario["Comentario"]["id"]));
echo $this->html->select("post_id","Post", array("options" => $Post , "type" => "text", "value" => $comentario["Comentario"]["post_id"]));
echo $this->html->input("comentario","Comentario", array("type" => "textarea", "value" => $comentario["Comentario"]["comentario"]));
echo $this->html->form("submit","Save changes");
echo $this->html->form("end");