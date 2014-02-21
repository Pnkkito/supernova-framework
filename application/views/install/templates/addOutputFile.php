<?php

$addOutputFile = '<h1>Add '.Inflector::singularize($modelName).'</h1>
<?php
echo $this->html->form("create");';
foreach ($fields as $field){
    $notInclude = array($defaultPK,'created','modified');
	if (!in_array($field['Field'], $notInclude)){
		switch ($field['Type']){
			case 'text': $returnedType = 'textarea'; break;
			default: $returnedType = 'text'; break;
		}
		if (!in_array($field['Field'], $foreingKeys)){
		    $addOutputFile.='echo $this->html->input("'.$field['Field'].'","'.Inflector::under_to_camel(strtolower($field['Field'])).'", array("type" => "'.$returnedType.'"));';
		}else{
		    $addOutputFile.='echo $this->html->select("'.$field['Field'].'","'.array_search($field['Field'],$foreingKeys).'", array("options" => $'.array_search($field['Field'],$foreingKeys).' , "type" => "'.$returnedType.'"));';
		}
	}
	$addOutputFile.="\n";
}

$addOutputFile.='echo $this->html->form("submit","Add new '.Inflector::singularize($modelName).'");
echo $this->html->form("end");';

echo $addOutputFile;