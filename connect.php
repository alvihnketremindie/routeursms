<?php
//========================================================================
//------------------------------------
//========================================================================
 define ("HOST","localhost");
 define ("USER", "root");
 define ("PASS","timezone!=grace");
 define ("BD","actvp");
 define ("DELAI","4000");
 define ("TENTATIVE","3");
 define ("RACINE","/var/lib/asterisk/agi-bin/ACTVP/"); 
 define ("URL","http://localhost:14013/cgi-bin/sendsms");
 define ("DLR","http://localhost/ROUTEURSMS/dlr.php?Content=%a&SOA=%p&DA=%P&date=%t&smscid=%i&dlrid=%I");
//========================================================================
//------------------------------------
//========================================================================
function conx_bd($host,$user,$pass)
{
  while(!($conx=mysql_connect($host,$user,$pass)))
  {
  }
  return $conx;
}
//========================================================================
//------------------------------------
//========================================================================
function fin()
{
 global $conx;
 mysql_close($conx);
 exit();
}
//========================================================================
//------------------------------------
//========================================================================
function insert_cdr()
{
  mysql_select_db("SMSQUEUES");
  $_GET["Content"] = addslashes($_GET["Content"]);
  if(isset($_GET["MMG"]) )
  {
    $sql="SELECT * FROM cdr WHERE (smscID='".$_GET["smscid"]."')AND(msgID='".$_GET["msgid"]."')AND(Content='".$_GET["Content"]."' )AND(SOA='".$_GET["SOA"]."' )";
    $result=mysql_query($sql);
	if($row=mysql_fetch_assoc($result))
	{
	   fin();
	}
  }
  $sql="INSERT INTO cdr VALUES(NULL,'".date("Y-m-d H:i:s")."','".$_GET["SOA"]."','".$_GET["DA"]."','".addslashes($_GET["Content"])."','".$_GET["smscid"]."','".$_GET["msgid"]."','','')";
  $result=mysql_query($sql);
  mysql_select_db(BD);
}
//========================================================================
//------------------------------------
//========================================================================
function sendSms($numero,$message)
{ 
//----------------------------------------Construction de l'url
 if(isset($_GET["MMG"]))
 { 
    $url=$_GET['MMG']."&Content=".urlencode($message)."&SOA=".$_GET['DA']."&DA=".$numero;
 }
 else
 {
  $content=urlencode($message); 
  $e=urlencode(DLR."&msgid=".$_GET["msgid"]);
  $url=URL."?from=".$_GET["DA"]."&to=".substr($numero, -11)."&text=$content&username=digital&password=digital&smsc=".$_GET["smscid"]."&dlr-url=".$e."&dlr-mask=31";
 }
 //----------------------------------------Lancemant de l'url
  echo $url."<br>";
  $fp=@fopen($url,"r");
  $returned=""; 
 if($fp)
 { 
  while(!feof($fp))
   {
      $returned=$returned.fgets($fp,1024);	
   }
 } 
 $tab_str=array("<html>","</html>","<head>","</head>","<title>","</title>","<body>","</body>","<br>");
  $returned=trim(str_replace($tab_str,"",$returned));
  echo $returned."<br>";
  //----------------------------------------------insertion bd
  mysql_select_db("SMSQUEUES");
  $sql="UPDATE cdr set mtContent='".addslashes($message)."' , dlrstatus='".addslashes($returned)."' WHERE (smscID='".$_GET["smscid"]."')AND(MsgID='".$_GET["msgid"]."') limit 1";
  mysql_query($sql);
  mysql_select_db(BD);
  echo("<b>Msg</b>=$message <b>To</b>=$numero <br>");
  //----------------------------------------------
}
//========================================================================
//------------------------------------
//========================================================================
function insertTable($table,$jeu,$res)
{
  global $rubriqueid;
  //----------------------------------------------------------------------
  if(substr($_GET["SOA"],0,2)=="00")
  {  
    $numero=substr($_GET["SOA"], 5);
  }
  else
  {
    $numero=$_GET["SOA"];
  }
  //----------------------------------------------------------------------
   $var="$rubriqueid-0-".$numero."-".$_GET["Content"]."-$res-$jeu-".$_GET["DA"];
   if( fopen("http://192.168.10.9:8084/ACTVP/asteriskserver/index.php?var=".urlencode($var),"r"))
    {
        $etat=1;
    }
    else
    {
        $etat=0;
    }
  //----------------------------------------------------------------------
  $sql="INSERT INTO ".$table." VALUES(NULL,'".date("Y-m-d H:i:s")."',$rubriqueid,0,'".$_GET["DA"]."','".$numero."','$jeu','".$_GET["Content"]."','$res','$etat')";
  mysql_query($sql);
  echo $sql.'<br>';
 if( $table!="callers")
 {
   $sql="INSERT INTO callers VALUES(NULL,'".date("Y-m-d H:i:s")."',$rubriqueid,0,'".$_GET["DA"]."','".$numero."','$jeu','".$_GET["Content"]."','$res','$etat')";
   mysql_query($sql);
   echo $sql.'<br>';
 }
}
//========================================================================
//------------------------------------
//========================================================================
function insertCash($table,$jeu,$res)
{
  global $rubriqueid;
  //----------------------------------------------------------------------
  if(substr($_GET["SOA"],0,2)=="00")
  {  
    $numero=substr($_GET["SOA"],2);
  }
  else
  {
    $numero=$_GET["SOA"];
  }
  //----------------------------------------------------------------------
  $sql="INSERT INTO ".$table." VALUES(NULL,'".date("Y-m-d H:i:s")."',$rubriqueid,0,'".$_GET["DA"]."','".$numero."','$jeu','".$_GET["Content"]."','$res','0')";
  mysql_query($sql);
  echo $sql.'<br>';
 }
//========================================================================
//------------------------------------
//========================================================================
function proba($prob)
{
  $x=rand(1,99)+$prob;
  if($x>=100)
  {
    return 1;     
  }
  else
  {
    return 0;
  }
}
//========================================================================
//------------------------------------
//========================================================================
function getErrorMsg($type)
{
  global $langue; 

  $sql="SELECT * FROM error_messages WHERE(type='$type')";
  $result=mysql_query($sql);
  $row=mysql_fetch_assoc($result);
  return $row[$langue];
}
//========================================================================
//------------------------------------
//========================================================================
function getMsg()
{
 global $langue;

 $sql="SELECT * FROM messages_new ORDER BY RAND()";
 $result=mysql_query($sql);
 $row=mysql_fetch_assoc($result);
 return $row[$langue];
}

function doPost($service) {
        header("Content-Type: text/html; Charset=UTF8");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$service);
        curl_setopt($ch, CURL_POST,1);
        curl_setopt($ch, CURL_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
        $res = curl_exec($ch);
        curl_close($ch);
echo $res;
}

?>
