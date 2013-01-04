<div id="content_left">
	<h4>Supernova Blackhole builder</h4>
	<p>Select the blackhole MVC you wish to create automagicaly in your application<p>
	<?=$this->html->form('create');?>
	<?=$this->html->select('tablename','Model name',array('options' => $tables));?>
	<?=$this->html->form('submit','Create this blackhole');?>
	<?=$this->html->form('end');?>
</div>