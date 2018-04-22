<?php

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

/************--------------------------- ROTAS PARA CATEGORIAS ADMIN ----------------------************/
/************-----------------------------------------------------------------------------************/

$app->get("/admin/categories",function (){

	User::verifyLogin();

	$page = new PageAdmin();

	$categories = Category::listAll();

	$page->setTpl("categories",array(
		"categories" =>$categories
	));

});

$app->get("/admin/categories/create",function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("categories-create");
});

$app->post("/admin/categories/create",function(){

	User::verifyLogin();

	$category  = new Category();

	$category->setData($_POST);
	
	
	$category->save();

	header('Location: /admin/categories');
	exit;

});

$app->get("/admin/categories/:idcategory/delete",function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header('Location: /admin/categories');
	exit;
});

$app->get("/admin/categories/:idcategory",function($idcategory){
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update",array(
		'category'=>$category->getValues()
	));


	
});

$app->post("/admin/categories/:idcategory", function($idcategory){
	User::verifyLogin();

	$category = new Category();
	
	$category->get((int)$idcategory);
	
	$category->setData($_POST);
	
	$category->save();	
	
	header('Location: /admin/categories');
	exit;
});


/************-----------------------------------------------------------------------------************/
/************-----------------------------------------------------------------------------************/

//Rota site principal
$app->get("/categories/:idcategory",function($idcategory){

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category",[
		'category'=>$category->getValues(),
		'products'=>[]
	]);

});



?>