<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use System_APService\clsSystem;

class IndexController extends AbstractActionController
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
		//執行查詢
		$strSQL = "select * from account";
		$data = $VTs->QueryData($strSQL);
		
		//debug，印出資料用
		$VTs->debug($data);
		//日期轉換
		$date = date("Y-m-d");
		$changeDate = $VTs->DateTime("ADyyyyMMdd_RCyyyMMdd",$date);
		$this->viewContnet['pageContent'] = $changeDate;
		//-----------BI結束------------ 
		
		//關閉資料庫連線
		$VTs->DBClose();
		//釋放
		$VTs=null;
		
		return new ViewModel($this->viewContnet);
    }
}
