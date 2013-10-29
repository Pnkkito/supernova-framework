<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title><?=SITE_NAME;?></title>

	<!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	<meta name="keywords" content="" />
	<meta name="description" content="" />

	<?php $publicPath = ( !isset($_SERVER['SupernovaCheck']) ) ? '/public/':'/' ;?>

	<!-- StyleSheets -->
	<link rel="stylesheet" href="<?=SITE_URL.Inflector::getBasePath().$publicPath.'css/';?>bootstrap.css" type="text/css" media="screen">
	<link rel="stylesheet" href="<?=SITE_URL.Inflector::getBasePath().$publicPath.'css/';?>style.css" type="text/css" media="screen">
	<link rel="stylesheet" href="<?=SITE_URL.Inflector::getBasePath().$publicPath.'css/';?>chosen.css" type="text/css" media="screen">

	<!-- JavaScripts -->
	<script src="<?=SITE_URL.Inflector::getBasePath().$publicPath.'js/';?>jquery-1.8.3.min.js"></script>
	<script src="<?=SITE_URL.Inflector::getBasePath().$publicPath.'js/';?>bootstrap.js"></script>
	<script src="<?=SITE_URL.Inflector::getBasePath().$publicPath.'js/';?>layout_actions.js"></script>

</head>
<body>
	
	<div id="wrapper">

		<header id="header">
			<!-- Header Left : Logo and Site name -->
			<div class="header-left">
				<!-- Header Logo --><img src='<?=SITE_URL.Inflector::getBasePath().$publicPath.'img/';?>snf_logo_t.png' />
				<!-- Site name --><h2><?=SITE_NAME;?></h2>
			</div>

		</header><!-- #header-->

		<section id="middle">

			<div id="container">
				<div id="content">

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

