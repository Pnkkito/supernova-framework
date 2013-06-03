<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title><?=SITE_NAME;?></title>

	<!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	<meta name="keywords" content="" />
	<meta name="description" content="" />

	<!-- StyleSheets -->
	<?=$this->html->includeCss("bootstrap");?>
	<?=$this->html->includeCss("style");?>
	<?=$this->html->includeCss("chosen");?>

	<!-- JavaScripts -->
	<?=$this->html->includeJs("jquery-1.8.3.min");?>
	<?=$this->html->includeJs("bootstrap");?>
	<?=$this->html->includeJs("chosen.jquery.min");?>
	<?=$this->html->includeJs("layout_actions");?>

	<!-- This are the javascripts executed in head -->
	<?=$this->html->scripts['head'];?>

</head>
<body>
	<!-- This are the javascripts executed in head -->
	<?=$this->html->scripts['start'];?>

	<div id="wrapper">

		<header id="header">
			<!-- Header Left : Logo and Site name -->
			<div class="header-left">
				<!-- Header Logo --><?=$this->html->image('snf_logo_t.png');?>
				<!-- Site name --><h2><?=SITE_NAME;?></h2>
			</div>

			<!-- Header Right : Action buttons -->
			<div class="header-right">

				<!-- Logout example button ( Uncomment to use it ) -->
				<!-- 
					<?=$this->html->link('<i class="icon-off icon-white"></i> Log-out','#', array('class' => 'btn btn-inverse'));?>
				-->

			</div>
		</header><!-- #header-->

		<section id="middle">

			<div id="container">
				<div id="content">

					<!-- Flash Message ( Read controller docs to know how to use it ) -->
					<?php echo $this->getMessage(); ?>

					<!-- Content for Layout -->
					<?php echo $content_for_layout; ?>

				</div><!-- #content-->
			</div><!-- #container-->

			<aside id="sideLeft">
				<ul class="nav nav-list nav-tabs nav-stacked sidebar-size">

					<!-- Elements contains layout parts, you can find them in /application/view/elements -->
					<?php echo $this->element('sidebar');?>

				</ul>
			</aside><!-- #sideLeft -->

		</section><!-- #middle-->

	</div><!-- #wrapper -->

	<footer id="footer">
		<strong>&copy; Supernova Framework 2013</strong>
	</footer><!-- #footer -->

</body>
</html>
<!-- This are the javascripts executed in head -->
<?=$this->html->scripts['end'];?>
