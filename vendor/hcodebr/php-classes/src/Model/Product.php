<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;


require_once('functions.php');
class Product extends Model{

	
	//Listar as categorias
	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT* from tb_products ORDER BY desproduct");
	}

	public static function checkList($list){

		foreach ($list as &$row ) {
			$p= new Product();
			$p->setData($row);

			//getValues ajusta o caminho das fotos para cada produto
			$row = $p->getValues();
		}

		return $list;
	}

	public function save(){
		$sql = new Sql();
		//echo json_encode($this->getValues());
		$results = $sql->select("
			CALL sp_products_save(:idproduct,:desproduct,:vlprice,:vlwidth,:vlheight,:vllenght,:vlweight,:desurl)", array(
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlprice"=>$this->getvlprice(),
			":vlheight"=>$this->getvlheight(),
			":vllenght"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl()

		));

		$this->setData($results[0]);




	}

	//Pesquisar um usario atraves de um ID
	public function get($idproduct){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct=:idproduct  ",array(
				":idproduct"=>$idproduct

			));

		$this->setData($results[0]);
	}

	public function delete(){

		$sql = new Sql();

		$sql->query("DELETE FROM tb_products WHERE idproduct=:idproduct",array(
			":idproduct"=>$this->getidproduct()
		));


	}

	public function checkPhoto(){
		if(file_exists($_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR. 
			"res" . DIRECTORY_SEPARATOR . 
			"site" . DIRECTORY_SEPARATOR . 
			"img" . DIRECTORY_SEPARATOR . 
			"products" . DIRECTORY_SEPARATOR . 
			$this->getidproduct().".jpg"
		)){

			$url = "/res/site/img/products/".$this->getidproduct().".jpg";

		}else{

			$url = "/res/site/img/product.jpg";
		}

		return $this->setdesphoto($url);
	}

	//Gambiarra pra fotos
	public function getValues(){
		//Seta a propriedade desphoto
		$this->checkPhoto();
		//Retorna os valores do produto( Classe MODEL)
		$values = parent::getValues();


		//Retorna os valoress
		return $values;
	}


	public function setPhoto($file){

		//Detectar o tipo da extensao do arquivo

		$extension= explode('.',$file['name']);

		$extension=end($extension);

		switch ($extension) {
			case 'jpg':
			case 'jpeg':
				$image = imagecreatefromjpeg($file["tmp_name"]);
				break;

			case "gif":
				$image = imagecreatefromgif($file["tmp_name"]);
				break;
			case "png":
				$image = imagecreatefrompng($file["tmp_name"]);
				break;
		}
		$dest = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR. 
			"res" . DIRECTORY_SEPARATOR . 
			"site" . DIRECTORY_SEPARATOR . 
			"img" . DIRECTORY_SEPARATOR . 
			"products" . DIRECTORY_SEPARATOR . 
			$this->getidproduct().".jpg";

		imagejpeg($image,$dest);
		//smart_resize_image($dest,null,195,243,false,$dest,false,true,100);

		imagedestroy($image);

		$this->checkPhoto();
	}

	//Retorna o produto atraves da URL
	public function getFromURL($desurl){

		$sql = new Sql();

		$rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1",[
			":desurl"=>$desurl
		]);

		$this->setData($rows[0]);

	}
	//Retorna as categorias que estao relacionadas com o produtos
	public function getCategories(){

		$sql = new Sql();

		return $sql->select("
			SELECT * from tb_categories a
			INNER JOIN tb_productscategories b ON a.idcategory=b.idcategory
			WHERE b.idproduct= :idproduct
		",[
			":idproduct"=>$this->getidproduct()
		]);
	}


	public static function getPage($page =1 ,$itemsPerPage = 10){

		$start=($page-1)*$itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT sql_calc_found_rows * 
			from tb_products 
			ORDER BY desproduct
			LIMIT $start,$itemsPerPage;

		");

		$resultsTotal = $sql->select("SELECT found_rows() as nrtotal;");

		return [
			"data" => $results,
			"total"=>(int)$resultsTotal[0]["nrtotal"],
			"pages"=>ceil($resultsTotal[0]["nrtotal"]/$itemsPerPage)
		];
	}

	public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
	{
		$start = ($page - 1) * $itemsPerPage;
		$sql = new Sql();
		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products
			WHERE desproduct LIKE :search 
			ORDER BY desproduct
			LIMIT $start, $itemsPerPage;
		", [
			':search'=>'%'.$search.'%'
		]);
		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];
	} 


}

?>