<?php

$outputModel = '<?php

class '.$modelName.' extends Model {
	
	public static $primaryKey = "'.$defaultPK.'";
	public static $displayField = "'.$defaultDisplayField.'";
	
	public static $belongsTo = array("'.implode( '","' , array_keys($foreingKeys) ).'");
	
}
';

echo $outputModel;
