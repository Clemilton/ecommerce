<?php

use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Product;
use \Hcode\Model\Cart;


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
	
	$numPage = (isset($_GET['page']) ) ? (int)$_GET['page']:1;
	$itemsPerPage=8;
	if($numPage<=0){//Caso esteja na primeira pagina e clique em Anterior
		header("Location: /categories/$idcategory");//Volta pra primeira pagina
		exit;
	}

	
	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($numPage,$itemsPerPage);


	$page = new Page();


	$pages=[];	

	for($i=1;$i<=$pagination['pages'];$i++){
		array_push($pages,[
			'link'=>'/categories/'.$category->getidcategory()."?page=".$i,
			'page'=>$i
		]);
	}
	$vetor = array(
		"num"=>$numPage
	);

	$page->setTpl("category",[
		'category'=>$category->getValues(),
		'products'=>$pagination['data'],
		'pages'=>$pages,
		'page'=>$vetor
	]);

});

//Detalhes do produto
$app->get("/products/:desurl",function($desurl){

	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail",[
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);

});
//Pagina de carrinhos
$app->get("/cart",function(){

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart");

});



?>