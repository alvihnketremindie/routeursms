<?php
 require("connect.php");
 $conx = mysql_connect(HOST,USER,PASS);
 mysql_select_db("SMSQUEUES");
  
 $concat = "";
 foreach($_GET as $key=>$value)
 {
   $concat = $key.":".$value." ".$concat;
 }

 $t = addslashes($concat);  $sql = "insert into dlr values('".$concat."')"; mysql_query($sql);

 $sql = "UPDATE cdr SET dlrstatus='".$_GET["Content"]."' WHERE (msgid='".$_GET["msgid"]."')";
 //mysql_query($sql);
?>
