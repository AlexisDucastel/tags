<?php
require_once __DIR__.'/common.php';

class Tag{
    
    private static function connect(){
        $m=new Mongo('mongodb://'.TAG_MONGO_DSN, array("persist" => "x"));
        return $m->selectCollection('tag',$_SERVER['REMOTE_USER']);
    }
    private function flatMongoId($m){
        $m['_id']=$m['_id']->{'$id'};
        return $m;
    }
    private function fetchMongoResult($res,$flatId=false){
        $result=array();
        foreach($res as $r) $result[]= $flatId ? $this->flatMongoid($r) : $r;
        return $result;
    }
    
    public function find($tags,$limit,$offset){
        if($tags==null)return array();
        $tagList=stringToTags($tags);
        
        // Connection Mongo
        $db=self::connect();
        
        $type=array();
        $tagArray=array();
        foreach($tagList as $tag){    
            if(substr($tag,0,2)=='t:') $type[]=substr($tag,2);
            // if(substr($tag,0,1)=='!') $tagArrayAll[]=substr($tag,1);
            else $tagArray[]=$tag;
        }
        
        $filter=array();
        if(count($type)>0)$filter['type']=array('$in'=>$type);
        if(count($tagArray)>0) $filter['tags']=array('$all'=>$tagArray);
        
        $items = $db->items->find($filter);
        
        //$items->sort(array('name'=>1));
        $items->limit($limit)->skip($offset);
        
        return $this->fetchMongoResult($items,true);
    }
    
    public function save($name,$type,$data,$tags,$id=null){
        if($tags==null)return false;
        $tagList=stringToTags($tags);

        $obj=array(
            'name'=>$name,
            'type'=>$type,
            'data'=>$data,
            'tags'=>$tagList,
        );
        if($id!=null)$obj['_id']=new MongoId($id);

        // Connection Mongo
        $db=self::connect();
        
        return $db->items->save($obj);
    }
    
    public function remove($id){
        // Connection Mongo
        $db=self::connect();
        return $db->items->remove(array( '_id'=>new MongoId($id)));
    }
    
    public static function describe(){
        return "?method=&amp;params=";
    }   
}