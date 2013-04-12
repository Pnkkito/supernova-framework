<?php

$outputModel = '<?php

class '.$modelName.' extends Model {

	var $index = "'.$index.'";
	var $primaryKey = "'.$defaultPK.'";
	var $displayField = "'.$defaultDisplayField.'";

}
';

echo $outputModel;
