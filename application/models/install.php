<?php

class Install extends Model {

	var $_table,$_tables;

	function searchForHasMany(){
		//Search in the model if have some relations in database
	}

	function searchForBelongsTo(){
		//Search in the database if some model has relations
	}

	function searchForHasTooMany(){
		//Search for n:n relations
	}

	function searchForRelations($table){
		//TODO: finish search for relations generator
		$this->_table = $table;
		$parsedTable = array(); //this should get [PREFIX.INDEX] = $name
		foreach ($tables as $tablename => $name){
			$parsedTable[substr($tablename, 0,4)."_ID"] = $name;
		}

		// $hasMany = searchForHasMany();
		// $belongsTo = searchForBelongsTo();
		// $hasTooMany = searchForHasTooMany();
		// $allRelations = array(	'hasMany' => $hasMany,
		// 							'belongsTo' => $belongsTo,
		// 							'hasTooMany' => $hasTooMany );
		return $allRelations;
	}

}

