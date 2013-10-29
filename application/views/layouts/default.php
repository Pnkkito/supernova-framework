<!DOCTYPE html>
<html>
  <head>
    <title>Arkangelkruel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <!-- Bootstrap -->
    <?=$this->html->includeCss("bootstrap/css/bootstrap");?>
    <?=$this->html->includeCss("bootstrap/css/bootstrap-responsive");?>
    <?=$this->html->includeCss("extras");?>
    <link href='http://fonts.googleapis.com/css?family=Ubuntu:300' rel='stylesheet' type='text/css'>
  </head>
   <body>

    <!-- <div class="visible-phone navbar navbar-inverse navbar-fixed-bottom posfix">
      <div class="navbar-inner">
        <div class="container">
          <button type="button" class="btn btn-navbar btn-left" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="#">Contacto</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li class="active"><a href="#">Home</a></li>
              <li><a href="#about">About</a></li>
              <li><a href="#contact">Contact</a></li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="#">Action</a></li>
                  <li><a href="#">Another action</a></li>
                  <li><a href="#">Something else here</a></li>
                  <li class="divider"></li>
                  <li class="nav-header">Nav header</li>
                  <li><a href="#">Separated link</a></li>
                  <li><a href="#">One more separated link</a></li>
                </ul>
              </li>
            </ul>
            <form class="navbar-form pull-right">
              <input name="contacttitletext" class="span2" type="text" placeholder="Email de contacto">
              <textarea name="contactbodytext" class="span2"></textarea>
              <input class="span2" type="password" placeholder="Password">
              <button type="submit" class="btn">Enviar mensaje</button>
            </form>
          </div>
        </div>
      </div>
    </div> -->
    
    <div class="container">

      <!-- Main hero unit for a primary marketing message or call to action -->
      <div class="hero-unit visible-desktop visible-tablet">
        <h1 class="title">ArkangelKruel</h1>
        <p>Código y otras hiervas</p>
        <!-- <p><a href="#" class="btn btn-primary btn-large">Learn more &raquo;</a></p> -->
      </div>
      <div class="visible-phone boxphone">
        <?=$this->html->image('arkangel_logo.png');?>
        <h2>ArkangelKruel</h2>
        <p>Código y otras hiervas</p>
      </div>
<h2 class="titles">Articulos</h2>
      <div class="content">
          <?php echo $this->getMessage(); ?>
          <?php echo $content_for_layout; ?>  
      </div>
<h2 class="titles">Acerca de</h2>
      <!-- Example row of columns -->
      <div class="row">
        <div class="span4">
          <a class="btn block" href="http://www.linkedin.com/profile/view?id=107023243">
            <span class="social-background social-linkedin">&nbsp;</span>
            <h4 class="subtitle">Currículum</h4>
          </a>
          <p>Productor de aplicaciones informáticas, gestor de bases de datos, desarrolladas directamente con PHP y frameworks como CakePHP, Yii, entre otros. Particularmente interesado en el diseño de bases de datos cliente/servidor y relacionales usando servidor MySQL. Interés constantes en proyectos migratorios, además de en su estrecha interacción con las productoras de bases de datos. </p>
        </div>
        <div class="span4">
          <a class="btn block" href="http://www.facebook.com/SupernovaFramework">
            <span class="social-background social-facebook">&nbsp;</span>
            <h4 class="subtitle">Supernova</h4>
          </a>
          <p>Supernova es un Framework MVC PHP liviano que puede manejar tanto projectos pequeños como otros de gran tamaño. Basado en VanillaPHP y CakePHP, nuestro proposito es generar grandes trabajos en pocas lineas de codigo y poca memoria.</p>
       </div>
        <div class="span4">
          <a class="btn block" href="https://twitter.com/ArkangelKruel">
            <span class="social-background social-twitter">&nbsp;</span>
            <h4 class="subtitle">Otros</h4>
          </a>
          <p>Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
        </div>
      </div>

      <hr>

      <footer>
        <p style="color: white;">Página hecha con <?=$this->html->image('snf_logo_t.png',array('style'=>'height:50px'));?></p>
      </footer>

    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="jquery-1.9.1.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>

  </body>
</html>