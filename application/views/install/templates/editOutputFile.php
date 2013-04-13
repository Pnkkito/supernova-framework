<?php

$editOutputFile = '<h1>Edit '.Inflector::singularize($modelName).'</h1>
<?php
echo $this->html->form("create");';
foreach ($fieldsUnparsed as $eachfield => $typefield){
	if (!empty($eachfield)){
		if ($eachfield == $defaultPK){
			$editOutputFile.='echo $this->html->input("'.$defaultPK.'","",array("type" => "hidden","value" => $'.strtolower($modelName).'["'.$modelName.'"]["'.$defaultPK.'"]));';
		}else{
			switch ($typefield){
				case 'text': $returnedType = 'textarea'; break;
				default: $returnedType = 'text'; break;
			}
			$editOutputFile.='echo $this->html->input("'.$eachfield.'","'.Inflector::under_to_camel(strtolower($eachfield)).'", array("type" => "'.$returnedType.'", "value" => $'.strtolower($modelName).'["'.$modelName.'"]["'.$eachfield.'"]));';
		}
		$editOutputFile.="\n";
	}
}
$editOutputFile.='echo $this->html->form("submit","Save changes");
echo $this->html->form("end");';

echo $editOutputFile;