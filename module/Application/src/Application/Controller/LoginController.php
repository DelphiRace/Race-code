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
		$VTs->initialization('oauth');
		
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
			
			
			$strSQL = "select * from account where userAc = '".$userAc."' and userPw = md5('".$userPw."')";
			$data = $VTs->QueryData($strSQL);
			
			//2.通過檢驗後，回傳登入Code與狀態
			if(!empty($data)){
				
				$uid = $data[0]["uid"];
				//驗證USER是否已存在Token
				$strSQL = "select uid from token where uid='".$uid."'";
				$TokenData = $VTs->QueryData($strSQL);
							
				//產生Token，會回傳Login_Code、Access_Token
				$loginArr = $VTs->CreatLoginCodeAndToken($uid);
				//存到Token資料表中，以供後續Oauth使用
				if(empty($TokenData)){
					$strSQL = "insert into token(uid,login_code,access_token,login_from) values('".$uid."','".$loginArr["Login_Code"]."','".$loginArr["Access_Token"]."','".$_SERVER["REMOTE_ADDR"]."')";
				}else{
					$strSQL = "update token set login_code='".$loginArr["Login_Code"]."',access_token='".$loginArr["Access_Token"]."',login_from='".$_SERVER["REMOTE_ADDR"]."',login_date='".date("Y-m-d H:i:s")."' where uid='".$uid."'";
				}
				//確定存取Token到資料表中
				$VTs->QueryData($strSQL);
				
				//紀錄SESSION
				$_SESSION["uid"] = $uid;
				$_SESSION["name"] = $data[0]["userName"];
				$_SESSION["mail"] = $data[0]["userMail"];
				$_SESSION["LoginCode"] = $loginArr["Login_Code"];
				
				$uidInfo["LoginCode"] = $loginArr["Login_Code"];
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
        //接收已於Google驗證好的資料
        //$VTs->debug($_POST);
//        //執行查詢
//        $strSQL = "select * from account";
//        $data = $VTs->QueryData($strSQL);
//        
//        //debug，印出資料用
//        $VTs->debug($data);
//        //日期轉換
//        $date = date("Y-m-d");
//        $changeDate = $VTs->DateTime("ADyyyyMMdd_RCyyyMMdd",$date);
//        $this->viewContnet['pageContent'] = $changeDate;
        $this->viewContnet['pageContent'] = $VTs->Data2Json($_POST);
        //-----------BI結束------------
        
        //關閉資料庫連線
        $VTs->DBClose();
        //釋放
        $VTs=null;
        
        return new ViewModel($this->viewContnet);
    }
    
}
