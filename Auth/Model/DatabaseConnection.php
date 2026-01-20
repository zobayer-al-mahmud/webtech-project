<?php 
class DatabaseConnection{

	function openConnection(){
		$db_host = getenv('DB_HOST');
		$db_host = $db_host !== false && $db_host !== '' ? $db_host : '127.0.0.1';
		$db_user = "root";
		$db_pass = "";
		$db_name = "university_events";
		$db_port = getenv('DB_PORT');
		$db_port = $db_port !== false && $db_port !== '' ? (int)$db_port : 3306;

		try {
			$connection = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
			if ($connection->connect_error) {
				return null;
			}
			return $connection;
		} catch (mysqli_sql_exception $e) {
			// Return null so callers can handle the error without a fatal.
			return null;
		}
	}

	function closeConnection($connection){
		$connection->close();
	}
}
?>
