<h3>Welcome to Supernova Framework</h3>

<p>To establishing database conections, you need to provide some info.<br/>
	Please fill the next fields with the correct data to<br/>
	make it possible</p>

<?php echo $this->html->form('create'); ?>
<?php echo $this->html->select('driver','Select your conection type', array('options' => array('mysql' => 'MySql conection'))); ?>
<?php echo $this->html->input('host','Enter your hostname',array('placehoder'=>'for example: localhost')); ?>
<?php echo $this->html->input('dbname','Enter your database name'); ?>
<?php echo $this->html->input('user','Enter your database username'); ?>
<?php echo $this->html->input('pass','Enter your database password'); ?>
<?php echo $this->html->input('prefix','Enter your table prefix'); ?>
<?php echo $this->html->form('submit','Check and save your settings');?>
<?php echo $this->html->form('end');?>