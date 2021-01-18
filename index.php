<?php
/**
 * PHP 7.0
 *
 *
 * @location /index.php
 */

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
define('DIR_ROOT', __DIR__);

require_once DIR_ROOT. '/lib/params.php';
require_once DIR_ROOT . '/vendor/autoload.php';
require_once DIR_ROOT . '/lib/handlers.php';

$returnData = ["error" => "0", "message" => ""];

$params = sanitize();

//check de la methode post, get, put, delete

$className = explode("/", trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), "/"));


if (count($className) >= 1) {
    //check de la methode post, get, put, delete
    $className = "api\\" . $className[0];
    $do = new $className;
    $returnData["error"] = "ERROR_00050";
    $returnData["message"] = "La méthode n'existe pas";

    $methodName = "data_" . strtolower($_SERVER['REQUEST_METHOD']);

    if (method_exists($className, $methodName)) {
        $returnData = $do->{$methodName}($params);
    }
} else {
    $returnData["error"] = "ERROR_00200";
    $returnData["message"] = "La méthode n'existe pas";
}

// on encode la sortie en json
header("Content-Type: application/json; charset=utf-8");
echo json_encode($returnData);
