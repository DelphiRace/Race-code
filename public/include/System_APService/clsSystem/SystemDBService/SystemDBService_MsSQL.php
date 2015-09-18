<?php		
	namespace SystemDBService;
	
	class clsDB_MySQL {
		//定義共用變數
		public $conn;
		//CreateDBConnection(sServer, sDatabase, sUser, sPassWord) as boolean
		//資料庫連線
		public function CreateDBConnection($sServer,$sDatabase,$sUser,$sPassWord){			
			$this->conn = new \mysqli($sServer, $sUser, $sPassWord,$sDatabase);
			if ($this->conn->connect_error) {
				$this->conn = false;
			}
			//回傳
			return $this->conn;
		}
		
		//用於單純INSERT、UPDATE、DELETE等
		//ExecuteNonQuery(sSqlText)
		public function ExecuteNonQuery($sSqlText){
			if( !empty($sSqlText) ){
				$stmt = $this->conn->query($sSqlText);
				return ($stmt)?true:false;
			}
		}
		
		//讀取資料 QueryData(sSqlText) as DataTable
		public function QueryData($sSqlText){
			$data = null;
			if( !empty($sSqlText) ){
				$stmt = $this->conn->query($sSqlText);
				$data = $this->Data2Array($stmt);
				
			}
			return $data;	
		}
		
		//建立Transcation機制 CreateMySqlTranscation
		public function Transcation(){
			$sSqlText = 'START TRANSACTION';
			$this->ExecuteNonQuery($sSqlText);
		}
		
		//Commit Transction機制 CommitMySqlTranscation
		public function Commit(){
			$sSqlText = 'COMMIT';
			$this->ExecuteNonQuery($sSqlText);
		}
		
		//Rollback Transction機制 RollbackMySqlTranscation
		public function Rollback(){
			$sSqlText = 'ROLLBACK';
			$this->ExecuteNonQuery($sSqlText);
		}
		
		//關閉資料庫連線 CloseConnection
		public function DBClose(){
			$this->conn->close();
		}
		
		//資料庫轉換資料
		private function Data2Array($DBQueryData){
			$data = null;
			if($DBQueryData){
				$i=0;
				if( $DBQueryData and $DBQueryData->num_rows){
					while ($ar = $DBQueryData->fetch_array(MYSQLI_ASSOC)) {
						$j=0;
						foreach($ar as $key=>$val){
							//echo $key."=>".$val;
							if( !empty($pk) ){
								$p = $ar[$pk];
								if($kind==0) $data[$p][$key]=$val;
								elseif($kind==1) $data[$p][$j]=$val;
							}
							else{
								if($kind==0) $data[$i][$key]=$val;
								elseif($kind==1) $data[$i][$j]=$val;
							}
							$j++;
						}
						$i++;
					}
				}
			}
			return $data;
		}
		
	}
?>