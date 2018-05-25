<?php

use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Product;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

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

	$address = new Address();
	$cart = Cart::getFromSession();
	

	if(isset($_GET['zipcode'])){
		$_GET['zipcode']=$cart->getdeszipcode();


	}

	if(isset($_GET['zipcode'])){
		$address->loadFromCEP($_GET['zipcode']);

		$cart->setdeszipcode($_GET['zipcode']);

		$cart->save();

		$cart->getCalculateTotal();

	}

	if (!$address->getdesaddress()) $address->setdesaddress('');
	if (!$address->getdesnumber()) $address->setdesnumber('');
	if (!$address->getdescomplement()) $address->setdescomplement('');
	if (!$address->getdesdistrict()) $address->setdesdistrict('');
	if (!$address->getdescity()) $address->setdescity('');
	if (!$address->getdesstate()) $address->setdesstate('');
	if (!$address->getdescountry()) $address->setdescountry('');
	if (!$address->getdeszipcode()) $address->setdeszipcode('');

	$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Address::getMsgError()
	]);

});

$app->post("/checkout",function(){

	User::verifyLogin(false);



	if (!isset($_POST['zipcode']) || $_POST['zipcode'] === '') {
		Address::setMsgError("Informe o CEP.");
		header('Location: /checkout');
		exit;
	}

	if (!isset($_POST['desaddress']) || $_POST['desaddress'] === '') {
		Address::setMsgError("Informe o endereço.");
		header('Location: /checkout');
		exit;
	}

	if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] === '') {
		Address::setMsgError("Informe o bairro.");
		header('Location: /checkout');
		exit;
	}

	if (!isset($_POST['descity']) || $_POST['descity'] === '') {
		Address::setMsgError("Informe a cidade.");
		header('Location: /checkout');
		exit;
	}

	if (!isset($_POST['desstate']) || $_POST['desstate'] === '') {
		Address::setMsgError("Informe o estado.");
		header('Location: /checkout');
		exit;
	}

	if (!isset($_POST['descountry']) || $_POST['descountry'] === '') {
		Address::setMsgError("Informe o país.");
		header('Location: /checkout');
		exit;
	}

	$user = User::getFromSession();

	$address = new Address();
	$_POST['deszipcode']=$_POST['zipcode'];
	$_POST['idperson']=$user->getidperson();

	$address->setData($_POST);

	$address->save();

	$cart = Cart::getFromSession();

	$cart->getCalculateTotal();



	$order = new Order();

	$order->setData([
		'idcart'=>$cart->getidcart(),
		'idaddress'=>$address->getidaddress(),
		'iduser'=>$user->getiduser(),
		'idstatus'=>OrderStatus::EM_ABERTO,
		'vltotal'=>$cart->getvltotal()
	]);

	$order->save();

	header("Location: /order/".$order->getidorder());
	exit;

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


$app->get('/forgot/',function(){

	//Inicializa a pagina. Nao tem header nem footer nessa pagina
	$page  = new Page();

	$page->setTpl("forgot");


});

$app->post('/forgot',function(){

	$user = User::getForgot($_POST["email"],false);

	header("Location: /forgot/sent");
	exit;
});

//Aviso de email envaiado
$app->get('/forgot/sent',function(){

	//Inicializa a pagina. Nao tem header nem footer no admin
	$page  = new Page();

	$page->setTpl("forgot-sent");


});
//Rota do link do email
$app->get('/forgot/reset',function(){
	//Valida o codigo
	$user = User::validForgotDecrypt($_GET["code"]);


	//Cria o template html
	$page  = new Page();

	$page->setTpl("forgot-reset",array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
});

$app->post("/forgot/reset",function(){
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

	$page  = new Page();

	$page->setTpl("forgot-reset-success");

});


$app->get("/profile",function(){
	
	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile",[
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);


});

$app->post("/profile",function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	if(!isset($_POST['desperson']) || $_POST['desperson']===''){
		User::setError("Preencha o seu nome");
		header("Location: /profile");
		exit;
	}

	if(!isset($_POST['desemail']) || $_POST['desemail']===''){
		User::setError("Preencha o seu nome");
		header("Location: /profile");
		exit;
	}

	if($_POST['desemail']!==$user->getdesemail()){

		if(User::checkLoginExists($_POST['desemail'])===true){

			User::setError("Este endereço de email já esta cadastrado");
			header("Location: /profile");
			exit;
		}

	
	}
	
	$_POST['iduser']=$user->getiduser();
	$_POST['inadmin']=$user->getinadmin();
	$_POST['despassword']=$user->getdespassword();
	$_POST['deslogin']=$_POST['desemail'];

	$user->setData($_POST);


	$user->update();

	$_SESSION[User::SESSION] = $user->getValues();
	$user->setSuccess("Dados alterados com sucesso");

	header('Location: /profile');

	exit;

});


$app->get("/order/:idorder", function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$page = new Page();

	$page->setTpl("payment", [
		'order'=>$order->getValues()
	]);
});


$app->get("/boleto/:idorder",function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);



	// DADOS DO BOLETO PARA O SEU CLIENTE
	$dias_de_prazo_para_pagamento = 10;
	$taxa_boleto = 5.00;
	$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
	$valor_cobrado = formatPrice($order->getvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
	$valor_cobrado = str_replace(",", ".",$valor_cobrado);
	$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

	$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
	$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
	$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
	$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
	$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS DO SEU CLIENTE
	$dadosboleto["sacado"] = $order->getdesperson();
	$dadosboleto["endereco1"] = $order->getdesaddress() . " " . $order->getdesdistrict();
	$dadosboleto["endereco2"] = $order->getdescity() . " - " . $order->getdesstate() . " - " . $order->getdescountry() . " -  CEP: " . $order->getdeszipcode();


	// INFORMACOES PARA O CLIENTE
	$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
	$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
	$dadosboleto["demonstrativo3"] = "";
	$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
	$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
	$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
	$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
	$dadosboleto["quantidade"] = "";
	$dadosboleto["valor_unitario"] = "";
	$dadosboleto["aceite"] = "";		
	$dadosboleto["especie"] = "R$";
	$dadosboleto["especie_doc"] = "";


	// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


	// DADOS DA SUA CONTA - ITAÚ
	$dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
	$dadosboleto["conta"] = "48781";	// Num da conta, sem digito
	$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

	// DADOS PERSONALIZADOS - ITAÚ
	$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

	// SEUS DADOS
	$dadosboleto["identificacao"] = "Hcode Treinamentos";
	$dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
	$dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
	$dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
	$dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

	// NÃO ALTERAR!
	$path  = $_SERVER['DOCUMENT_ROOT'] .DIRECTORY_SEPARATOR."res".DIRECTORY_SEPARATOR."boletophp".DIRECTORY_SEPARATOR."include".DIRECTORY_SEPARATOR;
	require_once($path."funcoes_itau.php");
	require_once($path."layout_itau.php");

});


$app->get("/profile/orders", function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile-orders", [
		'orders'=>$user->getOrders()
	]);

});
$app->get("/profile/orders/:idorder",function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$cart= new Cart();

	$cart->get((int) $order->getidcart());

	$cart->getCalculateTotal();

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile-orders-detail", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
	]);


});
?>