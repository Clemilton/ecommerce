<?php

use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Product;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;


//Rota Pagina Inicial
$app->get('/', function() {
    
	$products = Product::listAll();
	$productsWithPhotos = Product::checkList($products);

	$page = new Page();
	$page->setTpl("index", [
		'products'=>$productsWithPhotos
	]);

    
});

//Rota site principal
$app->get("/categories/:idcategory",function($idcategory){
	
	$numPage = (isset($_GET['page']) ) ? (int)$_GET['page']:1;
	$itemsPerPage=2;
	if($numPage<=0){//Caso esteja na primeira pagina e clique em Anterior
		header("Location: /categories/$idcategory");//Volta pra primeira pagina
		exit;
	}
	
	$category = new Category();

	$category->get((int)$idcategory);	
	//
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
	$cart->checkZipCode();
	$page = new Page();

	$page->setTpl("cart",[
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);

});
//Adicionar um produto ao carrinho
$app->get("/cart/:idproduct/add",function($idproduct){
	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$qtd= (isset($_GET['qtd']))?(int)$_GET['qtd']:1;

	for($i=0;$i<$qtd;$i++){
		
		$cart->addProduct($product);

	}
	header("Location: /cart");
	exit;
});

//Remover um produto do carrinho. Apenas 1
$app->get("/cart/:idproduct/minus",function($idproduct){
	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;
});

//Remover varios produtos do carrinho. 
$app->get("/cart/:idproduct/remove",function($idproduct){
	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product,true);

	header("Location: /cart");
	exit;
});

$app->post("/cart/freight",function(){

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header("Location: /cart");
	exit;
});

$app->get("/checkout", function(){

	User::verifyLogin(false);

	$cart = Cart::getFromSession();

	$address = new Address();

	$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()
	]);

});

$app->get("/login", function(){

	$page = new Page();

	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>(isset($_SESSION['registerValues']))? $_SESSION['registerValues']:['name'=>'','email'=>'','phone'=>'']
	]);

});

$app->post("/login", function(){

	try {

		User::login($_POST['login'], $_POST['password']);

	} catch(Exception $e) {

		User::setError($e->getMessage());

	}

	header("Location: /checkout");
	exit;

});

$app->get("/logout", function(){

	User::logout();

	header("Location: /login");
	exit;

});

$app->post("/register",function (){

	$_SESSION['registerValues']= $_POST;

	if(!isset($_POST['name']) || $_POST['name']==''){
		User::setErrorRegister("Preencha o nome do usuario");
		header("Location: /login");
		exit;
	}

	if(!isset($_POST['email']) || $_POST['email']==''){
		User::setErrorRegister("Preencha o seu email");
		header("Location: /login");
		exit;
	}
	
	if(!isset($_POST['password']) || $_POST['password']==''){
		User::setErrorRegister("Preencha a senha");
		header("Location: /login");
		exit;
	}

	if(User::checkLogin($_POST['email'])===true){
		User::setErrorRegister("Este endereco de email já esta sendo usado por outro usuario");
		header("Location: /login");
		exit;
	}
	
	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
	]);

	$user->save();

	User::login($_POST['email'], $_POST['password']);

	header("Location: /checkout");
	exit;
});


?>