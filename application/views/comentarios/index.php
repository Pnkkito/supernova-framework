<h1>Comentario List</h1>
<?php
echo $this->html->table($comentarios, array("actions" => true));
echo "<div class='buttons'>";
echo $this->html->link("<i class='icon-plus'></i> Add comentario",array("controller" => "comentarios","action" => "add"),array("class" => "btn"));
echo "</div>";