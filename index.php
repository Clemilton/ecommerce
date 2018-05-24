<?php 

session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;

$app = new Slim();

$app->config('debug', true);

require_once("functions.php");

//Rotas para o site principal
require_once("site.php");

//Rotas para o site da administracao
require_once("admin.php");

//Rotas e CRUD Users-Admin
require_once("admin-users.php");

//Rotas e CRUD Categories-Admin
require_once("admin-categories.php");

//Rotas e CRUD Products-Admin
require_once("admin-products.php");

$app->run();

 ?>