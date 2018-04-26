<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;



class Category extends Model{

	
	//Listar as categorias
	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT* from tb_categories ORDER BY descategory");
	}
	//Salva uma categoria no banco. 
	public function save(){
		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));

		$this->setData($results[0]);


		Category::updateFile();

	}

	//Pesquisar um usario  no banco atraves de um ID
	public function get($idcategory){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory=:idcategory  ",array(
				":idcategory"=>$idcategory

			));

		$this->setData($results[0]);
	}
	//Deleta um usuario atraves do id
	public function delete(){

		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory=:idcategory",array(
			":idcategory"=>$this->getidcategory()
		));

		Category::updateFile();
	}

	public static function updateFile(){

		$categories= Category::listAll();
		var_dump($categories);
		$html = [];

		foreach ($categories as $row) {
			echo "ola ".$row['descategory'];
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}
		$str= implode('',$html);
		
		file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR."categories-menu.html", $str);

	}
	//Função Dupla. Caso related == true  - Retorna os produtos sao da categoria
	//				Caso Related == false - Retorna os produtos que NAO sao da categoria
	public function getProducts($related= true){

		$sql = new Sql();
		if($related ===true){
			//Retorna todos os produtos que estao relacionados com a categoria
			return $sql->select("
				SELECT*  FROM tb_products WHERE idproduct IN(
					SELECT a.idproduct
					from tb_products a
					INNER JOIN tb_productscategories b on a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
				)
			",array(
				":idcategory"=>$this->getidcategory()
			));
		}else{
			//Retona todos os produtos que NAO estao relacionados com a categoria
			return $sql->select("
				SELECT*  FROM tb_products WHERE idproduct NOT IN(
					SELECT a.idproduct
					from tb_products a
					INNER JOIN tb_productscategories b on a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
				)
			",array(
				":idcategory"=>$this->getidcategory()
			));

		}
	}
	//Retorna um array
	//"data"	- Retorna os produtos que estao na pagina especifica.
	//"total"	- Retorna o total de produtos
	//"pages"	- Retorna a quantidade de paginas
	public function getProductsPage($page =1 ,$itemsPerPage = 3){

		$start=($page-1)*$itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT sql_calc_found_rows * 
			from tb_products a
			inner join tb_productscategories b on a.idproduct = b.idproduct
			inner join tb_categories c on c.idcategory = b.idcategory
			where c.idcategory=:idcategory
			limit $start,$itemsPerPage;

		",[
			":idcategory"=>$this->getidcategory()
		]);

		$resultsTotal = $sql->select("SELECT found_rows() as nrtotal;");

		return [
			"data" => Product::checkList($results),
			"total"=>(int)$resultsTotal[0]["nrtotal"],
			"pages"=>ceil($resultsTotal[0]["nrtotal"]/$itemsPerPage)
		];
	}
	//Adiciona o produto na categoria
	public function addProduct($idproduct){

		$sql = new Sql();

		$sql->query("INSERT INTO tb_productscategories(idcategory,idproduct) VALUES (:idcategory,:idproduct)",[
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$idproduct
		]);
	}
	//Remove um produto da categoria
	public function removeProduct($idproduct){

		$sql   = new Sql();
		$sql->query("DELETE FROM tb_productscategories WHERE idcategory =:idcategory AND idproduct=:idproduct",[
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$idproduct
		]);
	}


}

?>