<?php
require_once dirname(__DIR__).'/lib/tag-mongo.php';

//=============================================================================
// Auto code
//=============================================================================
function error($message){ die(json_encode(array('error'=>$message))); }

if(!isset($_REQUEST['method'])){
    die(Tag::describe());
}

$tag=new Tag();

$method=unmq($_REQUEST['method']);
if($method=='')error('No method provided');
if(!method_exists($tag,$method))error("Method $method doesn't exists !");


$params=unmq($_REQUEST['params']);
$params=($params!='')?json_decode($params):array();
if(!is_array($params))$params=array($params);

echo json_encode(
    call_user_func_array(array($tag,$method),$params)  
);