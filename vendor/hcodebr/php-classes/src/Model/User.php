<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{

	const SESSION ="User";

	//Chave de criptografia - PRECISA TER 16 CARACTERES
	const SECRET = "HcodePhp7_Secret";
	public static function login($login,$password){
		
		$sql = new Sql();
		//Procura o login no banco
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN",array(
			":LOGIN"=>$login
		));
		//Verifica o login
		if(count($results)===0){
			throw new \Exception("Usuario inexistente ou senha inválida", 1);
			
		}

		$data = $results[0];
		//verifica a senha
		if (password_verify($password,$data["despassword"])===true){
			$user = new User();
			//Seta os dados do banco no atributo values (class Model)
			$user->setData($data);

			//Define a variavel Sessao
			$_SESSION[User::SESSION] = $user->getValues();

			return $user;
		}
		else{

			throw new \Exception("Usuario inexistente ou senha inválida", 1);
		}
	}

	public static function verifyLogin($inadmin= true) {
		//Caso Nao esteja logado ou nao seja um usuario admin
		if(
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"]> 0
			||
			(bool)$_SESSION[User::SESSION]["inadmin"]!== $inadmin

		){
			//Retorna para a pagina de login
			header("Location: /admin/login");
			exit;
		}


	}

	public static function logout(){

		$_SESSION[User::SESSION]=NULL;
		
	}
	//Listar os uusarios
	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT* from tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
	}

	//Cadastrar um novo usuario
	public function save(){
		$sql = new Sql();


		$results = $sql->select("CALL sp_users_save(:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)",array(
			":desperson"	=>$this->getdesperson(),
			":deslogin"		=>$this->getdeslogin(),
			":despassword"	=>$this->getdespassword(),
			":desemail"		=>$this->getdesemail(),
			":nrphone"		=>$this->getnrphone(),
			"inadmin"		=>$this->getinadmin()
		
		));

		$this->setData($results[0]);


	}
	//Pesquisar um usario atraves de um ID
	public function get($iduser){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser",array(
				":iduser"=>$iduser

			));

		$this->setData($results[0]);
	}

	
	//Atualizar um usario.
	public function update(){
		$sql = new Sql();

	

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",array(
			":iduser"		=>$this->getiduser(),
			":desperson"	=>$this->getdesperson(),
			":deslogin"		=>$this->getdeslogin(),
			":despassword"	=>$this->getdespassword(),
			":desemail"		=>$this->getdesemail(),
			":nrphone"		=>$this->getnrphone(),
			"inadmin"		=>$this->getinadmin()
		
		));

		$this->setData($results[0]);


	}
	//Deletar um usuario
	public function delete(){

		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));


	}
	//retorna um emails
	public static function getForgot($email){
		// 1 - Verificar se o email esta cadastrado
		$sql = new Sql();

		$results = $sql->select("

			SELECT * 
			FROM tb_persons a
			INNER JOIN tb_users b USING(idperson)
			WHERE a.desemail = :email


			", array(
				":email"=>$email

			));


		if(count($results)==0){
			throw new \Exception("Nao foi possivel recuperar a senha", 1);
			
		}else{
			//Caso o email exista, fazer um link, com o id criptografado

			$data = $results[0];

			//Chama a procedure para redifinir senha.
			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser,:desip)",array(
				":iduser" => $data["iduser"],
				":desip"  => $_SERVER["REMOTE_ADDR"]
			));
			
			
			if(count($results2)===0){
				//Procedure nao retornou valores
				throw new \Exception("Nao foi possivel recuperar a senha", 1);
				
			}else{

				$dataRecovery = $results2[0];
				//Criptografia do link
				$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));
				//Link. O index deve ter uma rota para tratar esse codigo.
				$link= "http://www.ecommerce.com/admin/forgot/reset?code=$code";
				
				//Envio do email.
				$mailer = new Mailer($data["desemail"],$data["desperson"],"Redefinir senha da Hcode Store","forgot",
					array(
						"name"=>$data["desperson"],
						"link"=>$link
					));
				try{
				$mailer->send();
				}catch(\Exception $e){
					throw new Exception("Erro ao enviar o email: ".$e ,1);
					
				}

				return $data;
						
	
			}

		}

	}

	//Valida o codigo enviado pelo link
	public static function validForgotDecrypt($code)
	{
		//Descriptografa o codigo
		$idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);
		$sql = new Sql();
		$results = $sql->select("
			SELECT * 
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE 
				a.idrecovery = :idrecovery
			    AND
			    a.dtrecovery IS NULL
			    AND
			    DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
		", array(
			":idrecovery"=>$idrecovery
		));
		if (count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		else
		{
			return $results[0];
		}
	}




	public static function setForgotUsed($idrecovery){
		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery",array(
			":idrecovery" =>$idrecovery			
		));

	}

	public function setPassword($password){

		$sql = new Sql();
		//Atualiza 
		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser",array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));
	}




}

?>