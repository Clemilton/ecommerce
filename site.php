<?php

use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Product;


//Rota Pagina Inicial
$app->get('/', function() {
    
	$products = Product::listAll();
	$page = new Page();
	$page->setTpl("index", [
		'products'=>Product::checkList($products)
	]);

    
});


//Rota site principal
$app->get("/categories/:idcategory",function($idcategory){
		
	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category",[
		'category'=>$category->getValues(),
		'products'=>Product::checkList($category->getProducts())
	]);

});

?>