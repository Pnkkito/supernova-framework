<h1>Post List</h1>
<?php
echo $this->html->table($posts, array("actions" => true));
echo "<div class='buttons'>";
echo $this->html->link("<i class='icon-plus'></i> Add post",array("controller" => "posts","action" => "add"),array("class" => "btn"));
echo "</div>";