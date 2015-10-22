<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use System_APService\clsSystem;

class PageactionController extends AbstractActionController
{
	//不執行任何動作
	public function indexAction()
    {
		$this->viewContnet['pageContent'] = 'Please Select Your Action and Try Again!';
        return new ViewModel($this->viewContnet);
    }
	//取得選單
	public function getmenuAction()
    {
        //session_start();
		$VTs = new clsSystem;
		$VTs->initialization();
		
		//-----BI開始-----
		$action = array();
		$action["status"] = false;
		if(!empty($_POST["position"])){
			//取得Classroom系統權限
			$strSQL = "select uid,nid,parent_layer,class_style,href,click_action from sys_menu ";
            if($_SESSION["position"]){
                $strSQL .= "where (".$this->PositionStr2SQLCondition($_POST["position"]).") or (position like ('%0%')) ";
            }else{
                $strSQL .= "where (position like ('%0%')) ";
            }
			$strSQL .= "order by sequence,uid asc";
			$data = $VTs->QueryData($strSQL);
			//取得選單
			$action["menu"] = $data;
			$action["status"] = true;
		}else{
			$action["msg"] = 'Position is Error!';
           // $action["msg"] = $_POST;
		}
		$pageContent = $VTs->Data2Json($action);
		//-----BI結束-----
		$VTs = null;
		$this->viewContnet['pageContent'] = $pageContent;
        return new ViewModel($this->viewContnet);
    }
	
}
