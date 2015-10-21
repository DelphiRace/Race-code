<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use System_APService\clsSystem;

class LoginController extends AbstractActionController
{
	public $viewContnet;
	public $conn;
	public function __construct(){
		$this->viewContnet = array();
	}
    public function indexAction()
    {
		$VTs = new clsSystem;
		//先初始化
		//$VTs->initialization('oauth');
        $VTs->initialization();
		
		//-----------BI開始------------
		
		//設定資訊陣列
		$uidInfo = array();
		//資訊狀態
		$uidInfo["status"] = false;
		
		//檢測是否有傳入帳號與密碼
		if(!empty($_POST) and !empty($_POST["userAc"]) and !empty($_POST["userPw"])){
			$userAc = $_POST["userAc"];
			$userPw = $_POST["userPw"];
			//登入驗證步驟
			//1.檢驗帳號與密碼(若錯誤回傳錯誤)
			
			
			$strSQL = "select * from acl_user where userAc = '".$userAc."' and userPw = md5('".$userPw."')";
			$data = $VTs->QueryData($strSQL);
			
			//2.通過檢驗後，回傳登入Code與狀態
			if(!empty($data)){
				
				$uuid = $data[0]["uuid"];
				//驗證USER是否已存在Token
                //以下是SSO Token專用，目前暫時用不到
				/*$strSQL = "select uuid from token where uuid='".$uuid."'";
				$TokenData = $VTs->QueryData($strSQL);
							
				//產生Token，會回傳Login_Code、Access_Token
				$loginArr = $VTs->CreatLoginCodeAndToken($uuid);
				//存到Token資料表中，以供後續Oauth使用
				if(empty($TokenData)){
					$strSQL = "insert into token(uuid,login_code,access_token,login_from) values('".$uuid."','".$loginArr["Login_Code"]."','".$loginArr["Access_Token"]."','".$_SERVER["REMOTE_ADDR"]."')";
				}else{
					$strSQL = "update token set login_code='".$loginArr["Login_Code"]."',access_token='".$loginArr["Access_Token"]."',login_from='".$_SERVER["REMOTE_ADDR"]."',login_date='".date("Y-m-d H:i:s")."' where uuid='".$uuid."'";
				}
				//確定存取Token到資料表中
				$VTs->QueryData($strSQL);*/
				
				//紀錄SESSION
				//$_SESSION["uuid"] = $uuid;
				//$_SESSION["name"] = $data[0]["userName"];
				//$_SESSION["mail"] = $data[0]["userMail"];
				//$_SESSION["LoginCode"] = $loginArr["Login_Code"];
				
				//$uidInfo["LoginCode"] = $loginArr["Login_Code"];
				$uidInfo["uuid"] = $uuid;
                $uidInfo["userAc"] = $userAc;
                $uidInfo["name"] = $data[0]["userName"];
                $uidInfo["status"] = true;
			}else{ //2-1. 未通過驗證
				$uidInfo["error"] = 'The Accound is not Sing up!';
				$uidInfo["code"] = '2';
			}	
			//3.寫入LOG
			//$VTs->saveLog('loginAction','system','creatToken',$uidInfo["status"]);
		}else{//1-1 帳號密碼為空，回傳狀態
			$uidInfo["error"] = 'Accound or Password is Empty';
			$uidInfo["code"] = '1';
		}		
		$this->viewContnet["pageContent"] = $VTs->Data2Json($uidInfo);
		//-----------BI結束------------ 
		
		//關閉資料庫連線
		$VTs->DBClose();
		//釋放
		$VTs=null;
		
		return new ViewModel($this->viewContnet);
    }
    
    public function googlesigninAction()
    {
        $VTs = new clsSystem;
        //先初始化
        $VTs->initialization('oauth');
        
        //-----------BI開始------------
		//設定資訊陣列
		$uidInfo = array();
		$uidInfo["status"] = false;
		
        //接收已於Google驗證好的資料
		if($_POST["access_token"]){
			//1. 先與Google做AccessToken的認證
			$url="https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=".$_POST["access_token"];
			$googleLoginInfo = $VTs->Json2Data( $VTs->UrlDataGet($url) );
			$googleUserID = $googleLoginInfo->user_id;
			//1-1. 確認認證無誤
			if($googleUserID){
				//2. 執行查詢看資料庫是否已有新增過
				//執行查詢
				$strSQL = "select * from thirdparty_oauth where thirdparty_uid = '". $googleUserID ."'";
				$data = $VTs->QueryData($strSQL);
				
				//2-1. 沒有新增過，準備新增
				if(empty($data)){
					//執行新增
					$strSQL = "insert into thirdparty_oauth(thirdparty_uid, oauth_type, approveCode, approveStatus) values('".$googleUserID."',0,'1234',0)";
					$VTs->ExecuteNonQuery($strSQL);
					
					//2-2. 重新執行查詢，並取得UUID
					$strSQL = "select * from thirdparty_oauth where thirdparty_uid='".$googleUserID."'";
					$data = $VTs->QueryData($strSQL);
				}
								
				$uuid = $data[0]["uuid"];
				//驗證USER是否已存在Token
				$strSQL = "select uuid from token where uuid='".$uuid."'";
				$TokenData = $VTs->QueryData($strSQL);
							
				//產生Token，會回傳Login_Code、Access_Token
				$loginArr = $VTs->CreatLoginCodeAndToken($uuid);
				//存到Token資料表中，以供後續Oauth使用
				if($uuid){
					if(empty($TokenData)){
						$strSQL = "insert into token(uuid,login_code,access_token,login_from,login_type) values('".$uuid."','".$loginArr["Login_Code"]."','".$loginArr["Access_Token"]."','".$_SERVER["REMOTE_ADDR"]."',1)";
					}else{
						$strSQL = "update token set login_code='".$loginArr["Login_Code"]."',access_token='".$loginArr["Access_Token"]."',login_from='".$_SERVER["REMOTE_ADDR"]."',login_date='".date("Y-m-d H:i:s")."' where uuid='".$uuid."'";
					}
				}else{
					echo "System error";
					exit();
				}
				//確定存取Token到資料表中
				$VTs->QueryData($strSQL);
				
				//紀錄SESSION
				$_SESSION["uuid"] = $uuid;
				$_SESSION["name"] = $data[0]["userName"];
				$_SESSION["mail"] = $data[0]["userMail"];
				$_SESSION["LoginCode"] = $loginArr["Login_Code"];
				
				$uidInfo["LoginCode"] = $loginArr["Login_Code"];
				$uidInfo["status"] = true;
				
			}else{ //1-2. 未通過驗證
				$uidInfo["error"] = 'The Accound is not Sing up!';
				$uidInfo["code"] = '3';
			}
		}else{
			$uidInfo["error"] = 'You did not have google access token!';
			$uidInfo["code"] = '4';
		}
        $this->viewContnet['pageContent'] = $VTs->Data2Json($uidInfo);
        //-----------BI結束------------
        
        //關閉資料庫連線
        $VTs->DBClose();
        //釋放
        $VTs=null;
        
        return new ViewModel($this->viewContnet);
    }
    
	//Facebook Login
	public function facebooksigninAction()
    {
        $VTs = new clsSystem;
        //先初始化
        $VTs->initialization('oauth');
        
        //-----------BI開始------------
		//設定資訊陣列
		$uidInfo = array();
		$uidInfo["status"] = false;
		
        //接收已於Google驗證好的資料
		if($_POST["authResponse"]["accessToken"]){
			//1. 先與Google做AccessToken的認證
			$url="https://graph.facebook.com/v2.5/me?access_token=".$_POST["authResponse"]["accessToken"];
			$facebookLoginInfo = $VTs->Json2Data( $VTs->UrlDataGet($url) );
			$facebookUserID = $facebookLoginInfo->id;
			$facebookUserName = $facebookLoginInfo->name;
			
			//$VTs->debug($facebookLoginInfo);
			//exit();
			//1-1. 確認認證無誤
			if($facebookUserID){
				
				//2. 執行查詢看資料庫是否已有新增過
				//執行查詢
				$strSQL = "select * from thirdparty_oauth where thirdparty_uid = '". $facebookUserID ."'";
				$data = $VTs->QueryData($strSQL);
				
				//2-1. 沒有新增過，準備新增
				if(empty($data)){
					//執行新增
					$strSQL = "insert into thirdparty_oauth(thirdparty_uid,userName,oauth_type, approveCode, approveStatus) values('".$facebookUserID."','".$facebookUserName."',1,'1234',0)";
					$VTs->ExecuteNonQuery($strSQL);
					
					//2-2. 重新執行查詢，並取得UUID
					$strSQL = "select * from thirdparty_oauth where thirdparty_uid='".$facebookUserID."'";
					$data = $VTs->QueryData($strSQL);
				}
				
				$uuid = $data[0]["uuid"];
				//驗證USER是否已存在Token
				$strSQL = "select uuid from token where uuid='".$uuid."'";
				$TokenData = $VTs->QueryData($strSQL);
				
				//產生Token，會回傳Login_Code、Access_Token
				$loginArr = $VTs->CreatLoginCodeAndToken($uuid);
				//存到Token資料表中，以供後續Oauth使用
				if($uuid){
					if(empty($TokenData)){
						$strSQL = "insert into token(uuid,login_code,access_token,login_from,login_type) values('".$uuid."','".$loginArr["Login_Code"]."','".$loginArr["Access_Token"]."','".$_SERVER["REMOTE_ADDR"]."',1)";
					}else{
						$strSQL = "update token set login_code='".$loginArr["Login_Code"]."',access_token='".$loginArr["Access_Token"]."',login_from='".$_SERVER["REMOTE_ADDR"]."',login_date='".date("Y-m-d H:i:s")."' where uuid='".$uuid."'";
					}
				}else{
					echo "System error";
					exit();
				}
				//確定存取Token到資料表中
				$VTs->QueryData($strSQL);
				
				//紀錄SESSION
				$_SESSION["uuid"] = $uuid;
				$_SESSION["name"] = $data[0]["userName"];
				$_SESSION["mail"] = $data[0]["userMail"];
				$_SESSION["LoginCode"] = $loginArr["Login_Code"];
				
				$uidInfo["LoginCode"] = $loginArr["Login_Code"];
				$uidInfo["status"] = true;
				
			}else{ //1-2. 未通過驗證
				$uidInfo["error"] = 'The Accound is not Sing up!';
				$uidInfo["code"] = '6';
			}
			
		}else{
			$uidInfo["error"] = 'You did not have Facebook access token!';
			$uidInfo["code"] = '5';
		}
        $this->viewContnet['pageContent'] = $VTs->Data2Json($uidInfo);
        //-----------BI結束------------
        
        //關閉資料庫連線
        $VTs->DBClose();
        //釋放
        $VTs=null;
        
        return new ViewModel($this->viewContnet);
    }
}
