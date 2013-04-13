<?php

$url = Inflector::generateUrl(array('controller'=>'install','action'=>'ajaxRequest'));

$selectModel = <<<EOL
	// $("select").change(function(){
	// 	var value = $(this).val();
	// 	$("#ajaxBox").hide();
	// 	$("#submitButton").prop("disabled",true);
	// 	$.ajax({
	// 		type: 'POST',
	// 		url: '{$url}',
	// 		data: { data : { action : 'getRelatedModels', value : value } },
	// 		success: function(response){
	// 			$("#ajaxBox").html(response).slideDown();
	// 			$("#submitButton").prop("disabled",false);
	// 		}
	// 	});
	// });

	$("select").chosen();
EOL;

$this->html->includeScript($selectModel,'end');

?>

<h3>Supernova Blackhole</h3>

<p>The Blackhole its the mechanism that generate automagically your
	model, controller, and views files for your selected model.<br/>
	Will generate the entire CRUD files(Create, Read, Update, Delete)
	to made easy to make your apps</p>

<p>Select the model you wish to generate automagicaly inside the Blackhole</p>

<?=$this->html->form('create');?>
<?=$this->html->select('tablename','Model name',array('options' => $tables,'empty' => 'Choose your model...'));?>
<div id="ajaxBox" style="clear:both;height: 0px;"></div>
<br/><br/>
<?=$this->html->form('submit','Send this model to the Blackhole',array('id' => 'submitButton'));?>
<?=$this->html->form('end');?>
