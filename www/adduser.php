<?php
require_once __DIR__.'/../lib/common.php';
if(isset($_POST['user'],$_POST['pwd'])){
    $user=unmq($_POST['user']);
    $pwd=unmq($_POST['pwd']);
    $hash=md5("$user:tag:$pwd");
    $htdigest='';
    foreach(file("../auth/.htdigest") as $line){
        if(trim($line)=='')continue;
        if(substr($line,0,strlen($user)+1)=="$user:")continue;
        $htdigest.="$line\n";
    }
	$htdigest.="$user:tag:$hash\n";
    file_put_contents("../auth/.htdigest",$htdigest);
    header('Location: /');
    exit();
}
?><html>
<body>
<form method="post">
    Nom : <input type="text" name="user" value=""><br>
    Pwd : <input type="password" name="pwd" value=""><br>
    <input type="submit" value="Ok">
</form>
</body>
</html>