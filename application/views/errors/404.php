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

		</header><!-- #header-->

		<section id="middle">

			<div id="container">
				<div id="content">

					<!-- Flash Message ( Read controller docs to know how to use it ) -->
					<?php echo $this->getMessage(); ?>

					<!-- Content for Layout -->
					<h2>Error 404</h2>
					<h3>OOPS, Something went wrong</h3>
					<p>The page you are trying to access seems to no longer exists.</p>
					<p>Check you address bar, meaby you got a mistaken address</p>
					
				</div><!-- #content-->
			</div><!-- #container-->

		</section><!-- #middle-->

	</div><!-- #wrapper -->

	<footer id="footer">
		<strong>&copy; Supernova Framework 2013</strong>
	</footer><!-- #footer -->

</body>
</html>
<!-- This are the javascripts executed in head -->
<?=$this->html->scripts['end'];?>
