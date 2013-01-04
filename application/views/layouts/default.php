<!DOCTYPE html>
<html>
	<head>
		<title><?=SITE_NAME;?></title>
		<!-- Bootstrap -->
		<?=$this->html->includeCss("bootstrap");?>
		<?=$this->html->includeCss("extra");?>
		<?=$this->html->includeJs('jquery-1.8.3.min');?>
		<?=$this->html->includeJs("bootstrap");?>
		<!-- <link href='http://fonts.googleapis.com/css?family=Ubuntu+Condensed' rel='stylesheet' type='text/css'> -->
	</head>
	<body>
		<div class="header">
			<div class="header-left">
				<table>
					<td>
						<?=$this->html->image('snf_logo_t.png', array('style' => 'padding-left: 20px; padding-top: 5px;'));?>
					</td>
					<td>
						<h3 style="font-weight: normal; margin-left: 200px; margin-top: 15px; color: #FFF"><?=SITE_NAME;?></h3>
					</td>
				</table>
			</div>
			<div class="header-right">
				<?=$this->html->link('<i class="icon-off icon-white"></i> Log-out',array('controller' => 'users', 'action' => 'logout', 'management' => true), array('class' => 'btn btn-inverse'));?>
			</div>
		</div>
		
		<div class="fullcontent">
 			<div class="sidebar">
				<ul class="nav nav-list nav-tabs nav-stacked sidebar-size">
					<li><?=$this->html->link('<i class="icon-align-justify"></i> Instalation',array('controller' => 'pages', 'action' => 'install', 'management' => true,'admin'));?></li>
				</ul>
			</div>
			<div class="content">
					<?php $_message = $this->getMessage();
					if ($_message){
					?>
					<!--  start message-green -->
					<div class="alert">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<?=$_message; ?>
					</div>
					<!--  end message-green -->
					<?php } ?>
					<?= $content_for_layout; ?>  
			</div>
		</div>
  </body>
</html>


