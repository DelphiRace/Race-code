<?php
	//跨網域請求
	header("Access-Control-Allow-Origin: *");
	include("include/config.php");
	use System_APService\clsSystem;
	
	$VTs = new clsSystem;
	//先初始化
	$VTs->initialization('oauth');
	
	$strSQL = "select b.uid,b.userName,b.userMail,login_date from token a ";
	$strSQL .= "left join account b on a.uid = b.uid ";
	$strSQL .= " where a.access_token = '".$_POST["access_token"]."'";
	$data = $VTs->QueryData($strSQL);

	//$basis->debug($data[0]);
	echo $VTs->Data2Json($data[0]);
	$VTs = null;
?>