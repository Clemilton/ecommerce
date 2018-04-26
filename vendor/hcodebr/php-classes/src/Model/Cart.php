<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;


class Cart extends Model{

	const SESSION = "Cart";

	public static function getFromSession(){

		$cart = new Cart();
		//Se a secao Existir e se o ID foi definido entao carrega o carrinho
		if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart']>0){
			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
			var_dump($cart->getValues());
			echo "Caso 1 ".session_id();
			exit;
		}else{

			//Tenta pegar a partir do session_id o carrinho
			$cart->getFromSessionID();
				
			var_dump($cart->getValues());
			echo "Caso 2 ".session_id();
			exit;

		
			if(!(int)$cart->getidcart()>0){
				$data=[
					"dessessionid"=>session_id()
				];
			}
			if(User::checkLogin(false)==true){
				$user = User::getFromSession();

				$data['iduser'] = $user->getiduser();
			}

			$cart->setData($data);

			$cart->save();

			$cart->setToSession();

		}
		return $cart;
	}



	public function setToSession(){

		$_SESSION[Cart::SESSION] = $this->getValues();
	}
	//Retorna o carrinho, atraves do  session_id()
	public function getFromSessionID(){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid =:dessessionid",[
			':dessessionid'=>session_id()
		]);
		//Se a query retornou algum valor, seta os dados do carrinho na instancia
		if(count($results)>0){
			$this->setData($results[0]);
		}
	}

	//Retorna um Cart fornecendo o ID
	public function get($idcart){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart =:idcart",[
			':idcart'=>$idcart
		]);

		//Se a query retornou algum valor, seta os dados do carrinho na instancia
		if(count($results)>0){
			$this->setData($results[0]);
		}
	}
	
	public function save(){
		$sql = new Sql();

		$results = $sql->SELECT("CALL sp_carts_save(:idcart,:dessessionid,:iduser,:deszipcode,:vlfreight,:nrdays)",[
			':idcart' => $this->getidcart(),
			':dessessionid' =>$this->getdessessionid(),
			':iduser'=>$this->getiduser(),
			':deszipcode'=>$this->getdeszipcode(),
			':vlfreight'=>$this->getvlfreight(),
			':nrdays'=>$this->getnrdays()
		]);

		$this->setData($results[0]);
	}
}


?>