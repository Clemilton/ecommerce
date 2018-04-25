<?php

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;


/************--------------------------- ROTAS PARA CATEGORIAS ADMIN ----------------------************/
/************-----------------------------------------------------------------------------************/

//Pagina Categorias
$app->get("/admin/categories",function (){

	User::verifyLogin();

	$page = new PageAdmin();

	$categories = Category::listAll();

	$page->setTpl("categories",array(
		"categories" =>$categories
	));

});
//Pagina Cadastro Categoria
$app->get("/admin/categories/create",function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("categories-create");
});
//Pagina Envio Categoias
$app->post("/admin/categories/create",function(){

	User::verifyLogin();

	$category  = new Category();
	//Setando os dados vindos do POST. (Classe model)
	$category->setData($_POST);
	//Salvando os dados no banco
	$category->save();

	header('Location: /admin/categories');
	exit;

});
//Rota para deletar uma categoria identificada pelo $idcacategory
$app->get("/admin/categories/:idcategory/delete",function($idcategory){

	User::verifyLogin();

	$category = new Category();
	//Pega uma categoria do banco
	$category->get((int)$idcategory);

	$category->delete();

	header('Location: /admin/categories');
	exit;
});
//Pagina update categoria
$app->get("/admin/categories/:idcategory",function($idcategory){
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update",array(
		'category'=>$category->getValues()
	));


	
});
//Post Update categoria
$app->post("/admin/categories/:idcategory", function($idcategory){
	User::verifyLogin();

	$category = new Category();
	
	$category->get((int)$idcategory);
	
	$category->setData($_POST);
	
	$category->save();	
	
	header('Location: /admin/categories');
	exit;
});



$app->get("/admin/categories/:idcategory/products",function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	

	$page = new PageAdmin();

	
	$page->setTpl("categories-products",[
		"category"=>$category->getValues(),
		"productsRelated"=>$category->getProducts(),
		"productsNotRelated"=>$category->getProducts(false)
	]);

});



$app->get("/admin/categories/:idcategory/products/:idproduct/add",function($idcategory,$idproduct){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->addProduct($idproduct);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});

$app->get("/admin/categories/:idcategory/products/:idproduct/remove",function($idcategory,$idproduct){
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->removeProduct($idproduct);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});




/************-----------------------------------------------------------------------------************/
/************-----------------------------------------------------------------------------************/




?>