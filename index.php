<?php 

session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

$app = new Slim();

$app->config('debug', true);


//Rota Pagina Inicial
$app->get('/', function() {
    
	$page  = new Page();

	$page->setTpl("index");

    
});

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
//Tela esqueci senha
$app->get('/admin/forgot/',function(){

	//Inicializa a pagina. Nao tem header nem footer no admin
	$page  = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");


});


/************--------------------------- ROTAS PARA ESQUECI A SENHA ----------------------************/
/************-----------------------------------------------------------------------------************/

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

$app->get("/categories/:idcategory",function($idcategory){

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category",[
		'category'=>$category->getValues(),
		'products'=>[]
	]);

});


$app->run();

 ?>