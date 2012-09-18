<?php
require_once __DIR__.'/../conf/config.php';
function unmq($value){ return (get_magic_quotes_gpc()?stripslashes($value):$value); }
function stripAccent($string){
    $replaces=array(
        'À','Á','Â','Ã','Ä','Å','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ñ','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','Ý',
        'à','á','â','ã','ä','å','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ'
    );
    $replacement=array(
        'A','A','A','A','A','A','C','E','E','E','E','I','I','I','I','N','O','O','O','O','O','U','U','U','U','Y',
        'a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','u','y','y'
    );
    return str_replace($replaces,$replacement,$string);
}
function stringToTags($string){
    $string=preg_replace('/[,;\\s]/',' ',$string);
    $string=preg_replace('/\\s+/',' ',$string);
    $string=preg_replace('/^\\s+|\\s+$/','',$string);
    $string=stripAccent($string);
    return explode(' ',$string);
}
