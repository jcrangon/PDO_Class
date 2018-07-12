<?php
$dbdata=[
"driver"=>"mysql",
"serveur"=>"localhost",
"base"=>"DBname",
"port"=>"3306",
"user"=>"user",
"pass"=>"pass",
"charset"=>"utf8mb4",
"options"=>array(
	//PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
	//PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES   => false
	)
];
?>
