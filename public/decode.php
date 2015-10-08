<?php
	//跨網域請求
	header("Access-Control-Allow-Origin: *");
	include("include/config.php");
	use System_APService\clsSystem;
	
	$VTs = new clsSystem;
	//先初始化
	$VTs->initialization('oauth');
	
	$strSQL = "select access_token from token where login_code = '".$_POST["login_code"]."'";
	$data = $VTs->QueryData($strSQL);
	
	//print_r($data);
	echo $VTs->Data2Json($data[0]);
	$VTs = null;
?>