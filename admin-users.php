<?php

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;


//Lista usuarios
$app->get('/admin/users/',function(){

	
	User::verifyLogin();

	$page  = new PageAdmin();

	$users = User::listAll();

	
	$page->setTpl("users",array(
		"users"=>$users
	));

});
//Tela de cadastro usuarios
$app->get('/admin/users/create',function(){

	 //Inicializa a pagina. Nao tem header nem footer no admin

	User::verifyLogin();

	$page  = new PageAdmin();


	$page->setTpl("users-create");


});
//Tela de deletar usuario
$app->get('/admin/users/:iduser/delete',function($iduser){

	
	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");

	exit;
});

//Tela para editar um usuario
$app->get('/admin/users/:iduser',function($iduser){

	 //Inicializa a pagina. Nao tem header nem footer no admin
	User::verifyLogin();

	$user = new User();

	//insere dados em  user a partir de um ID
	$user->get((int)$iduser);


	$page  = new PageAdmin();

	$page->setTpl("users-update",array(
		"user"=>$user->getValues()
	));

});

//Post cadastro usuario
$app->post('/admin/users/create',function(){
	User::verifyLogin();

	
	$user = new User();
	//Pega a opcao admin
	$_POST["inadmin"]= (isset($_POST["inadmin"]))?1:0;

	//Criptografia da senha
	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
        "cost"=>12
    ]);
	//salva os dados na instancia user
	$user->setData($_POST);
	//salva no banco
	$user->save();

	header("Location: /admin/users");
	exit;
	 
});


//Atualizar um usuario
$app->post('/admin/users/:iduser',function($iduser){

	
	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"]= (isset($_POST["inadmin"]))?1:0;

	//Pega o usario conforme o id
	$user->get((int)$iduser);


	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");

	exit;
});


?>