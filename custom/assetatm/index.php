<?php

    require('config.php');
    if(!empty($conf->of->enabled)) {
        header('location:'.dol_buildpath('/of/liste_of.php',1));
    }
    else{
        header('location:'.dol_buildpath('/assetatm/liste.php',1));
    }
