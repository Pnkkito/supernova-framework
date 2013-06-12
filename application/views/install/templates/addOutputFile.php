<?php

$addOutputFile = '<h1>Add '.Inflector::singularize($modelName).'</h1>
<?php
echo $this->html->form("create");';
foreach ($fieldsUnparsed as $eachfield => $typefield){
	if (!empty($eachfield)){
		if ($eachfield != $defaultPK){
			switch ($typefield){
				case 'text': $returnedType = 'textarea'; break;
				default: $returnedType = 'text'; break;
			}
			$addOutputFile.='echo $this->html->input("'.$eachfield.'","'.Inflector::under_to_camel(strtolower($eachfield)).'", array("type" => "'.$returnedType.'"));';
		}
	}
	$editOutputFile.="\n";
}

$addOutputFile.='echo $this->html->form("submit","Add new '.Inflector::singularize($modelName).'");
echo $this->html->form("end");';

echo $addOutputFile;