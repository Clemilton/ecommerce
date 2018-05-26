<?php

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;



$app->get('/admin/users/:iduser/password',function($iduser){
	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-password",[
		"user"=>$user->getValues(),
		"msgError"=>User::getError(),
		"msgSuccess"=>User::getSuccess()

	]);



});


$app->post("/admin/users/:iduser/password", function($iduser){

	User::verifyLogin();

	if (isset($_POST['despassword']) && $_POST['despassword']===''){
		User::setError("Preencha a nova senha.");
		header("Location: /admin/users/$iduser/password");
		exit;
	}

	if (isset($_POST['despassword-confirm']) && $_POST['despassword-confirm']===''){
		User::setError("Preencha a confirmação da nova senha.");
		header("Location: /admin/users/$iduser/password");
		exit;
	}

	if ($_POST['despassword'] !== $_POST['despassword-confirm']){
		User::setError("Confirme a nova senha corretamente.");
		header("Location: /admin/users/$iduser/password");
		exit;
	}

	$user = new User();
	
	$user->get((int)$iduser);
	
	$user->setPassword(User::getPasswordHash($_POST['despassword']));
	
	User::setSuccess("Senha alterada com sucesso.");
	
	header("Location: /admin/users/$iduser/password");
	exit;
});

//Lista usuarios
$app->get('/admin/users',function(){

	
	User::verifyLogin();

	$search =  (isset($_GET['search'])) ? $_GET['search']:"";
	$page=(isset($_GET['page']))?(int)$_GET['page']:1;

	if ($search != '') {
		$pagination = User::getPageSearch($search, $page,10);
	} else {
		$pagination = User::getPage($page);
	}

	$pages=[];

	for ($x = 0; $x < $pagination['pages']; $x++){
		array_push($pages, [
			'href'=>'/admin/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);
	}
	$page  = new PageAdmin();

	
	$page->setTpl("users",array(
		"users"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
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