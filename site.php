<?php

use \Hcode\Page;


//Rota Pagina Inicial
$app->get('/', function() {
    
	$page  = new Page();

	$page->setTpl("index");

    
});



?>