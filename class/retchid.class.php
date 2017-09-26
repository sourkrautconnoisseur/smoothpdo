<?php

$GetIncludedFiles = get_included_files();
if(!preg_match("~Constants.php~",$GetIncludedFiles)){
	// THROW ERROR
}


class retchid{
	protected $DebugLine = 0;
	protected $MySQLConnection = null;
	protected $MySQLErrorCount = 0;
	protected $MySQLLastError = null;
	
	public function __construct{
	}
	
	public function NewMySQLConnection(){
		foreach (SQL_CONNECTION_ARRAY as $KeyName => $Data){
			if(empty($Data)){
				$this->WriteDebugStream('$KeyName not set, please check includes/constants.php');
				return false;
			}
		}
		$DSN = "mysql:host=". SQL_CONNECTION_ARRAY['HOST'] .";dbname=" . SQL_CONNECTION_ARRAY['DATABASE'];
		try{
			$this->MySQLConnection = new PDO($DSN,SQL_CONNECTION_ARRAY['USERNAME'],SQL_CONNECTION_ARRAY['PASSWORD']);
			$this->WriteDebugStream('Connected to: ' . SQL_CONNECTION_ARRAY['DATABASE'] . " on " . SQL_CONNECTION_ARRAY['HOST']);
			return $this->MySQLConnection;
		}catch(PDOException $MySQLError){
			$this->WriteDebugStream('PHP Encountered an error connecting.');
			$this->MySQLErrorCount++;
			$this->MySQLLastError = $MySQLError;
		}		
	}
	
	public function DestroyMySQLConnection(){
		$listQuery = 'SHOW PROCESSLIST -- ' . uniqid( 'pdo_mysql_close ', 1);
	   	$threadList  = $this->MySQLConnection->query( $listQuery )->fetchAll( PDO::FETCH_ASSOC );
	   	foreach( $threadList as $uniqueThread ){
	        if ( $uniqueThread['Info'] === $listQuery ){
	           	$this->MySQLConnection->query('KILL ' . $uniqueThread['Id'] );
	           	unset( $this->MySQLConnection );
	           	$this->WriteDebugStream("Sucessfully Disconnected from " . SQL_CONNECTION_ARRAY['DATABASE'] . " on " . SQL_CONNECTION_ARRAY['HOST']);
	       		return true;
	        }
	    }
	    return false;
	}
	
	
	protected function WriteDebugStream($Content){
		if(DEBUG_VARIABLES['JS']){
		echo JSCRIPT['OPEN'] . JSCRIPT['CONS'] . "Line" . $this->DebugLine . ":" . $Content . JSCRIPT['CFUN'] . JSCRIPT['CLOSE'];
		}
		if(DEBUG_VARIABLES['PHP']){
			if(is_array($Content)){
				print_r("Line " . $this->DebugLine . ": " . $Content . "\n");
			}else{
				print("Line " . $this->DebugLine . ": " . $Content . "\n");
			}
		}
		$this->DebugLine++;
	}


}

?>
