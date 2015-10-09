<?php
	//跨網域請求
	header("Access-Control-Allow-Origin: *");
	include("include/config.php");
	use System_APService\clsSystem;
	
	$VTs = new clsSystem;
	//先初始化
	$VTs->initialization('oauth');
	
	
	$strSQL = "select * from token where access_token = '".$_POST["access_token"]."'";
	$data = $VTs->QueryData($strSQL);
	
	//從別的系統登入
	if($data[0]["login_type"] == 1){
		$strSQL = "select a.uuid,a.userName,a.userMail,b.login_date from thirdparty_oauth a ";
		$strSQL .= "left join token b on a.uuid = b.uuid ";
		$strSQL .= "where a.uuid = '".$data[0]["uuid"]."'";
		$data = $VTs->QueryData($strSQL);
	}else if($data[0]["login_type"] == 0){
		$strSQL = "select b.uuid,b.userName,b.userMail,login_date from token a ";
		$strSQL .= "left join account b on a.uuid = b.uuid ";
		$strSQL .= " where a.uuid = '".$data[0]["uuid"]."'";
	}
	//$basis->debug($data[0]);
	echo $VTs->Data2Json($data[0]);
	$VTs = null;
?>