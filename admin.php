<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

//Rota pagina inicial admin
$app->get('/admin/', function() {
	//Verifica o Login
    User::verifyLogin();
	$page  = new PageAdmin();
	//var_dump($_SESSION[User::SESSION]);
	$page->setTpl("index");

});

//Rota login admin
$app->get('/admin/login', function() {
    //Inicializa a pagina. Nao tem header nem footer no admin
	$page  = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);


	$page->setTpl("login");

});

//Tentativa de login
$app->post('/admin/login',function() {

	//metodo estatico que verifica o login(Class User)
	User::login($_POST["login"],$_POST["password"]);

	//Caso chegue aqui, pode entrar no admin;
	header("Location: /admin");
	exit;
});


//Rota para logout
$app->get('/admin/logout',function(){
	User::logout();

	header("Location: /admin/login");
	exit;
});


/************--------------------------- ROTAS PARA ESQUECI A SENHA ----------------------************/
/************-----------------------------------------------------------------------------************/
//Tela esqueci senha
$app->get('/admin/forgot/',function(){

	//Inicializa a pagina. Nao tem header nem footer nessa pagina
	$page  = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");


});

$app->post('/admin/forgot',function(){

	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;
});

//Aviso de email envaiado
$app->get('/admin/forgot/sent',function(){

	//Inicializa a pagina. Nao tem header nem footer no admin
	$page  = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");


});
//Rota do link do email
$app->get('/admin/forgot/reset',function(){
	//Valida o codigo
	$user = User::validForgotDecrypt($_GET["code"]);


	//Cria o template html
	$page  = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset",array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
});

$app->post("/admin/forgot/reset",function(){
	//Valida o codigo
	$forgot = User::validForgotDecrypt($_POST["code"]);

	//Pega o id do usuario que deseja recuperar a senha
	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();
	//Pega um usuario a traves do id e seta em $user
	$user->get((int) $forgot["iduser"]);

	//Criptografa a senha
	$password = password_hash($_POST["password"],PASSWORD_DEFAULT,[
		"cost"=>12
	]);
	//Cadastra a senha no banco;
	$user->setPassword($password);

	$page  = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");




});

/************-----------------------------------------------------------------------------************/
/************-----------------------------------------------------------------------------************/






?>