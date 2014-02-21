<?php

$editOutputFile = '<h1>Edit '.Inflector::singularize($modelName).'</h1>
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
		    $editOutputFile.='echo $this->html->input("'.$field['Field'].'","'.Inflector::under_to_camel(strtolower($field['Field'])).'", array("type" => "'.$returnedType.'", "value" => $'.strtolower($modelName).'["'.$modelName.'"]["'.$field['Field'].'"]));';
		}else{
		    $editOutputFile.='echo $this->html->select("'.$field['Field'].'","'.array_search($field['Field'],$foreingKeys).'", array("options" => $'.array_search($field['Field'],$foreingKeys).' , "type" => "'.$returnedType.'", "value" => $'.strtolower($modelName).'["'.$modelName.'"]["'.$field['Field'].'"]));';
		}
	}else{
	    $editOutputFile.='echo $this->html->input("'.$defaultPK.'","",array("type" => "hidden","value" => $'.strtolower($modelName).'["'.$modelName.'"]["'.$defaultPK.'"]));';
	}
	$editOutputFile.="\n";
}

$editOutputFile.='echo $this->html->form("submit","Save changes");
echo $this->html->form("end");';

echo $editOutputFile;