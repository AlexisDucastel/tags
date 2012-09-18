<?php

function connect(){
    mysql_connect('localhost',TAG_MYSQL_USER,TAG_MYSQL_PASS);
    mysql_select_db(TAG_MYSQL_DB);
}

function mysql_insert_object_list($table,$objectList){
    $lines=array();
    $c=true;
    $columns = array();
    
    foreach($objectList as $object){
        $a       = (array) $object;    
        $values  = array();
        
        foreach($a as $k=>$v){
            if($c) $columns[]="`$k`";
            $values[]="'".mysql_real_escape_string($v)."'";
        }    
        $c=false;
        $lines[]='('.implode(',',$values).')';
    }
    
    $sql="INSERT INTO `$table` (".implode(',',$columns).") VALUES ".implode(',',$lines);
    
    mysql_query($sql);
    
    return mysql_insert_id();
}
function mysql_insert_object($table,$object){
    return mysql_insert_object_list($table,array($object));
}


class Tag{
    
    public function remove($id){
        $id=intval($id);
        
        // Connection MySQL
        connect();
        
        mysql_query("DELETE FROM tag WHERE id=$id;");
        mysql_query("DELETE FROM item WHERE id=$id;");
        unlink("../data/$id.type");
        unlink("../data/$id.data");
        return true;
    }
    
    // https://tag.mdns.fr/service.php?method=getData&params=[8]
    public function getData($id){
        $id=intval($id);
        $file="../data/$id.data";
        if(!file_exists($file)){
            header("HTTP/1.0 404 Not Found");
            exit();
        }
        switch(@file_get_contents("../data/$id.type")){
            case 'text': header('Content-Type: text/plain; charset=utf-8'); break;
        }
        readfile($file);
        exit();
    }
    
    // https://tag.mdns.fr/service.php?method=save&params=[%22Rien%20du%20tout%22,%22text%22,%22Ma%20super%20id%C3%A9e%22,%22idea,rien%22]
    public function save($name,$type,$data,$tags){
        // Connection MySQL
        connect();
        
        $id=mysql_insert_object('item',array(
            'name'    =>$name,
            'type'    =>$type
        ));
        
        file_put_contents("../data/$id.type",$type);
        file_put_contents("../data/$id.data",$data);
        
        $tagLines=array();
        foreach(stringToTags($tags) as $tag){
            $tagLines[]=array('tag'=>$tag,'id'=>$id);
        }
        
        mysql_insert_object_list('tag',$tagLines);
        
        return $id;
    }
    
    // https://tag.mdns.fr/service.php?method=find&params=[%22idea%22]
    public function find($tags){
        if($tags==null)return array();
        $tagList=stringToTags($tags);
        
        // Connection MySQL
        connect();
        
        // échappement de tous les tags
        $tagListMySQL= array_map('mysql_real_escape_string',$tagList);
        
        // Préparation de la requète
        $sql="SELECT t.id,COUNT(*) AS weight,i.`name`,i.`type` FROM tag AS t
                INNER JOIN item AS i ON (i.id=t.id)
                WHERE tag IN ('".implode("','",$tagListMySQL)."')
                GROUP BY id
            	ORDER BY weight DESC
        ";
        $sql="SELECT t.id,t.`weight`,i.`name`,i.`type` FROM item AS i
            INNER JOIN (
            	SELECT id,COUNT(*) AS weight FROM tag WHERE tag IN ('".implode("','",$tagListMySQL)."') GROUP BY id
            ) AS t ON (t.id=i.id)
            ORDER BY weight DESC
        ";
        
        $results=array();
        $res=mysql_query($sql);
        while($row=mysql_fetch_assoc($res)) $results[]=$row;
        
        return $results;
    }
    
    public static function describe(){
        return basename(__FILE__)."?method=&amp;params=";
    }
    
    
    
}