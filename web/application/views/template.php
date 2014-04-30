<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <title><?php echo $title; ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <!-- Le styles -->
        <link href="<?php echo base_url(); ?>/css/bootstrap.css" rel="stylesheet">
        <style type="text/css">
            body {
                padding-top: 60px;
                padding-bottom: 40px;
            }
            .sidebar-nav {
                padding: 9px 0;
            }
        </style>
        <link href="<?php echo base_url(); ?>/css/bootstrap-responsive.css" rel="stylesheet">

        <link href="<?php echo base_url(); ?>/css/my.css" rel="stylesheet">
        <?php
        if (isset($css)) {
            printf("<style>\n%s</style>\n", $css);
        }
        ?>

        <script type="text/javascript">var base_url="<?php echo base_url() . index_page() . '/'; ?>";</script>

        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <!-- Le fav and touch icons -->
        <link rel="shortcut icon" href="<?php echo base_url(); ?>/ico/favicon.ico">
        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo base_url(); ?>/ico/apple-touch-icon-144-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo base_url(); ?>/ico/apple-touch-icon-114-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo base_url(); ?>/ico/apple-touch-icon-72-precomposed.png">
        <link rel="apple-touch-icon-precomposed" href="<?php echo base_url(); ?>/ico/apple-touch-icon-57-precomposed.png">
    </head>

    <body>
        <div class="navbar navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container-fluid">
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <a class="brand" href="#">Все вебкамеры Истры</a>
                    <div class="btn-group pull-right" id="loginFormDiv">
<?php if ($logged) $this->load->view($logged); ?>
                    </div>
                    <div class="nav-collapse collapse">
                        <ul class="nav">
                            <li class="active"><a href="<?php echo base_url(); ?>">На главную</a></li>
                            <li><a href="http://Истра.рф" target="_blank">Истра.рф</a></li>
                            <!--                            <li><a href="#about">О проекте</a></li>-->
                            <li><a href="http://istranet.ru/services/video-watch">Контакты</a></li>
                            <?php
                            if (isset($adminmenu)) {
                                $this->load->view('admin/menu');
                            }
                            ?>
                        </ul>
                    </div><!--/.nav-collapse -->
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row-fluid">
<?php if (isset($left)) { ?>
                    <div class="span3">
    <?php $this->load->view($left); ?>
                    </div><!--/span-->
                    <div class="span9">
                        <div class="hero-unit">
                            <h1>истра.рф</h1>
                        </div>
                        <div class="row-fluid">
    <?php
    if (isset($user_error)) {
        echo $user_error;
    }
    ?>
    <?php $this->load->view($content); ?>
                        </div><!--/row-->
                    </div><!--/span-->
                        <?php } else { ?>
                    <div class="span12">
                        <div class="hero-unit">
                            <h1>истра.рф</h1>
                        </div>
                        <div class="row-fluid">
                    <?php
                    if (isset($user_error)) {
                        echo $user_error;
                    }
                    ?>
    <?php $this->load->view($content); ?>
                        </div><!--/row-->
                    </div><!--/span-->
<?php } ?>
            </div><!--/row-->

            <hr>

            <footer>
                <p>Created by <a href="http://webdeva.ru">Webdeva</a> specially for истра.рф</p>
            </footer>

        </div><!--/.fluid-container-->

        <!-- Le javascript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="<?php echo base_url(); ?>/js/libraries/jquery.js"></script>
        <script src="<?php echo base_url(); ?>/js/libraries/bootstrap-transition.js"></script>
        <script src="<?php echo base_url(); ?>/js/libraries/bootstrap-alert.js"></script>
        <script src="<?php echo base_url(); ?>/js/libraries/bootstrap-modal.js"></script>
        <script src="<?php echo base_url(); ?>/js/libraries/bootstrap-dropdown.js"></script>
        <script src="<?php echo base_url(); ?>/js/libraries/bootstrap-carousel.js"></script>
        <script src="<?php echo base_url(); ?>/js/libraries/bootstrap-collapse.js"></script>
        <script src="<?php echo base_url(); ?>/js/libraries/jquery.jeditable.mini.js"></script>
        <script src="<?php echo base_url(); ?>/js/libraries/jquery.jeditable.checkbox.js"></script>


        <script src="<?php echo base_url(); ?>/js/my.js"></script>
<?php
if (isset($js)) {
    printf("<script>\n%s</script>\n", $js);
}
?>


    </body>
</html>


