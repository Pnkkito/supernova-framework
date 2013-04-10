<?php

$indexOutputFile = '<h1>'.$modelName.' List</h1>
<?php
echo $this->html->table($'.strtolower(Inflector::pluralize($modelName)).', array("actions" => true));
echo "<div class=\'buttons\'>";
echo $this->html->link("<i class=\'icon-plus\'></i> Add '.strtolower($modelName).'",array("controller" => "'.strtolower(Inflector::pluralize($modelName)).'","action" => "add"),array("class" => "btn"));
echo "</div>";';

echo $indexOutputFile;