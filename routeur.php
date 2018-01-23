<?php
 //echo "=========================================================================================<br>"; 
 //echo "=====================<b> POWERED BY KYCHHAVE 2008 (c) </b>===============================<br>";
// echo "=========================================================================================<br>"; 
 require("connect.php");
 $conx=conx_bd(HOST,USER,PASS);
 $_GET['SOA']=str_replace('+','00',$_GET['SOA']);  
 $_GET['DA']=str_replace('+','',$_GET['DA']);
 insert_cdr();
 //=================================================
 mysql_select_db("SERVICES");
 $cont=strtoupper(trim($_GET["Content"]));
 //--------------------------------------RECHERCHE D'UN SERVICE
 $correspondance=0;
 $service=""; 
 $sql="SELECT * FROM sms WHERE (smscid='".$_GET["smscid"]."')AND((shortcode='0')OR(shortcode='".$_GET["DA"]."'))";
 $result=mysql_query($sql);
 while($row=mysql_fetch_assoc($result))
 {
    $tabstr=split(";",$row["keyword"]);
    foreach($tabstr as $elm)
    { 
      if (   strtoupper($elm)==substr( $cont,0,strlen($elm)   )    )
       {
	     if(strlen($elm)> $correspondance)
		 {
		   $correspondance=strlen($elm);
           $service=$row["url"];
           $libelle=$row["libelle"];
		 }
        }      
    }      
 }	 
 //---------------------------------------RECHERCHE D'UN SERVICE PAR DEFAUT
 if($service=="")
 {
   $sql="SELECT * FROM sms WHERE (smscid='".$_GET["smscid"]."')AND((shortcode='0')OR(shortcode='".$_GET["DA"]."'))AND(keyword='default') ";
   $result=mysql_query($sql);
   if( $row=mysql_fetch_assoc($result))
   {
      $service=$row["url"]; 
      $libelle=$row["libelle"];  
   }
 }
 //=======================================TRAITEMENT DU SERVICE 
 if($service!="")
 {  
  $service=$service."?Content=".urlencode($_GET["Content"])."&SOA=".urlencode($_GET["SOA"])."&DA=". urlencode($_GET["DA"])."&msgid=".urlencode($_GET["msgid"])."&smscid=".$_GET["smscid"]."&date=".urlencode($_GET["date"]);
  if(isset($_GET['MMG']))
  {
    $service=$service."&MMG=".urlencode($_GET["MMG"]);
  } 

print $service;
 // echo "<b>SERVICE</b>=".$libelle."<br>";
 // echo "<b>URL</b>=".$service."<br><br>";
 if( $libelle == "GRATTO_LINE" || $libelle == "SMS_BINGO" ) {doPost($service); exit; } 
  $handle = fopen($service, "r");
  if ($handle) 
  {
    while (!feof($handle)) 
    {
        $buffer = fgets($handle, 4096);
        echo $buffer."<br>";
    }
    fclose($handle);
  }
 }
 else
 {
	if ($_GET["smscid"] == "MTN_GH" or $_GET["smscid"] == "KASAPA_GH") sendSms($_GET["SOA"],"Sorry, the keyword used is incorrect. Please check the instructions of the service you would like to use. Thank you.");
	elseif($_GET["smscid"]  == "ZAIN_MG_0335600030") sendSms($_GET["SOA"], "Tapitra ny lalao Quiz vacances. Misaotra tamin ny fandraisanao anjara. Le jeu Quiz vacances a pris fin. Merci pour votre participation");
	else   sendSms($_GET["SOA"],"Desole, le mot cle utilise est incorrect. Veuillez verifier la syntaxe du service auquel vous desirez participer. Merci."); 
 }
 fin();



?>
