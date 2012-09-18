<?php
session_start();

if(!isset($_SERVER['REMOTE_USER'])){
    die('config error');
}

?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <title>Tag</title>
    <link href="js/google-code-prettify/prettify.css" type="text/css" rel="stylesheet" />
    <script type="text/javascript" src="js/google-code-prettify/prettify.js"></script>
    
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/jquery-ui.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.0/themes/base/jquery-ui.css">
    
    <script type="text/javascript">
        $(function(){ 
			// jQuery loaded callback
            $('#tag-search').focus();
		});
        
        function tagDelete(id){
            if(id==null)return;
            if(!confirm("Confirmer la suppression ?")) return false;
            api.remove(id,function(d){
                if(!d)return alert("Impossible de supprimer l'item");
                resetEditor();
                tagSearch();
            });
        }
        function tagEdit(obj){
            var e=$('#editorBg');
            e.find('[name=id]').val( obj!=null?obj._id:'null');
            e.find('.delete').hide();
            if(obj!=null){
                e.find('.delete').show();
                e.find('[name=name]').val(obj.name);
                e.find('[name=type]').val(obj.type);
                e.find('[name=data]').val(obj.data);
                e.find('[name=tags]').val(obj.tags);
            }
            e.show();
            $('#editor [name=name]').focus();
        }
        function resetEditor(){
            $('#editorBg').hide();
            $('#editorBg form')[0].reset();
        }
        function tagSave(f){
            var id=$(f).find('[name=id]').val();
            if(id=='null')id=null;
            var name=$(f).find('[name=name]').val();
            var type=$(f).find('[name=type]').val();
            var data=$(f).find('[name=data]').val();
            var tags=$(f).find('[name=tags]').val();
            console.log(id);
            api.save(name,type,data,tags,id,function(d){
                if(!d)alert("Erreur d'enregistrment");
                else {
                    $('#tag-search').val(tags);
                    resetEditor();
                    tagSearch();
                }
            });
            return false;
        }
        
        function webLinkClick(e){
            
            window.open($(this).data('url'));
            e.preventDefault();
            e.stopImmediatePropagation();
            e.stopPropagation();
        }
        function tagSearch(o){
            o=o||0;
            var tagList=$('#tag-search').val();
            
            $('#results').html('<img src="img/load.gif">');
            api.find(tagList,10,0,function(r){
                $('#results').html('');
                if($(r).length==0){
                    $('#results').html('<span class="empty">pas de résultats</span>');
                }
                else {
                    for(i in r){
                        var item=$('.result.template').clone().removeClass('template');
                        var id=r[i]._id;
                        item.find('.edit').data('o',r[i]).click(function(e){
                            tagEdit($(this).data('o'));
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            e.stopPropagation();
                        });
                        item.find('.name').text(r[i].name);
                        item.find('.type').text(r[i].type);
                        item.find('.data').text(r[i].data.replace(/\t/g,"    "));
                        
                        switch(r[i].type){
                            case 'youtube':
                                item.find('.item').append(
                                    $('<img class="icon" src="img/world_go.png">')
                                        .data('url',r[i].data+'').click(webLinkClick)
                                );
                                
                                item.find('.data').html('').append(
                                    $('<iframe width="560" height="315" frameborder="0" allowfullscreen></iframe>')
                                        .attr('src','https://www.youtube.com/embed/'+(/[\?&]v=([^&]+)/).exec(r[i].data)[1])
                                );
                                break;
                            case 'url':
                                item.find('.item').append(
                                    $('<img class="icon" src="img/world_go.png">').click(function(e){
                                        window.open(r[i].data);
                                        e.preventDefault();
                                        e.stopImmediatePropagation();
                                        e.stopPropagation();
                                    })
                                );
                                break;
                            case 'bash':
                            case 'php':
                            case 'html':
                            case 'css':
                            case 'js':
                                item.find('.data').addClass('prettyprint');
                                break;
                            default:
                                break;
                        }
                        item.find('.item').disableSelection();
                        item.find('.tags').html('');
                        for(j in r[i].tags){
                            item.find('.tags').append(
                                $('<span class="tag"/>').text(r[i].tags[j]).disableSelection()
                            );
                        }
                        $('#results').append(item);
                    }
                    prettyPrint();
                }
            });
            return false;
        }
        
        api={
        	a:function(method,params,callback){
        		if(params==null) params=[];
        		if(callback==null)callback=function(){};
        		$.ajax({
        			url:'ajax.php',
        			dataType:'json',
        			type:'POST',
        			data:{method:method,params:JSON.stringify(params)},
        			success:function(data){
        				callback(data);
        			}
        		});
        	},
        	find:function(tags,limit,offset,callback){ return this.a('find',[tags,limit,offset],callback); },
        	save:function(name,type,data,tags,id,callback){ return this.a('save',[name,type,data,tags,id],callback); },
        	remove:function(id,callback){ return this.a('remove',[id],callback); }
            
        };
	</script>
    <style>
        html,body { padding:0; margin:0; }
        .template { display:none; }
        #results { margin-top: 5px; }
        .result { border: solid 1px silver; margin: 4px; }
        .item-data { display:none; }
        .data { width:100%; margin: 3px 0; padding: 3px; }
        .clickable { cursor:pointer; }
        .item { font-weight: bold; background:#EEF; padding: 3px;  }
        .tags { background:#EEF; padding: 5px; }
        .tags .tag { background: #DDF; border: solid 1px #CCF; border-radius: 3px; padding: 2px 5px; margin: 0 3px; font-size:0.8em;  }
        .empty { color:silver; font-style: italic; margin: 5px;}
        #create { display:none; }
        .icon {position:relative;top:3px;}
        pre.prettyprint { border:none; }
        #editorBg { display:none; }
        #editorCover { position:fixed; top:0; left:0; width:100%; height: 100%; background:gray; opacity: 0.7; }
        #editor { z-index:10; position:fixed; top:5%; left:50%; margin-left:-47%; width:94%; background:white; padding: 10px 5px; }
    </style>
</head>
<body>
<img src="img/load.gif" style="display:none;">
<table style="width:100%;">
    <tr>
        <td width="70">Chercher : </td>
        <td><form onsubmit="return tagSearch();"><input type="text" id="tag-search" value="" style="width:100%;"/></form></td>
        <td width="20" style="text-align:right;"><img src="img/add.png" onclick="tagEdit(null);" class="icon clickable" /></td>
    </tr>
</table>


<div id="results"></div>
<div class="result template">
    <div class="item clickable" onclick="$(this).next().toggle();">
        <div style="float:right"><img src="img/page_white_edit.png" class="icon edit clickable"></div>
        [<span class="type">type</span>]
        <span class="name">name</span>
    </div>
    <div class="item-data">
        <pre class="data">blablabla</pre>
        <div class="tags"><span class="tag">tag 1</span><span class="tag">tag 2</span></div>
    </div>
</div>


<!-- <div class="clickable" onclick="$(this).next().toggle();">Ajouter un item</div> -->
<div id="editorBg">
    <div id="editorCover"></div>
    <div id="editor">
    <form onsubmit="return tagSave(this);">
    <input type="hidden" name="id" value="null" />
    <table style="width:100%;">
        <tr>
            <th width="50">Name</th>
            <td><input name="name" style="width:95%" type="text" value=""></td>
        </tr>
        <tr>
            <th>Type</th>
            <td><input name="type" type="text" value=""></td>
        </tr>
        <tr>
            <th>Data</th>
            <td><textarea name="data" style="width:95%" rows="5"></textarea></td>
        </tr>
        <tr>
            <th>Tags</th>
            <td><input name="tags" style="width:95%" type="text" value=""></td>
        </tr>
        <tr>
            <th></th>
            <td>
                <input type="submit" value="Sauver">
                <input type="button" value="Annuler" onclick="$('#editor form')[0].reset();$('#editorBg').hide();">
                <input type="button" class="delete" value="Supprimer" onclick="tagDelete($('#editor [name=id]').val());">
            </td>
        </tr>
    </table>
    </form>
    </div>
</div>
</body>
</html>
