<?php

require_once 'database.php';

function database_config() {
	$dbcfg_holder = new DATABASE_CONFIG();
        return $dbcfg_holder->default;
}

function database() {
        $cfg = database_config();
	$db_url = "mysql:host=" . $cfg['host'] . ";dbname=" . $cfg['database'];
	$db = new PDO($db_url, $cfg['login'], $cfg['password'], array(
		PDO::ATTR_PERSISTENT => true,
		PDO::MYSQL_ATTR_LOCAL_INFILE => 1 /* Won't work on some versions of PHP, may need to custom compile */
	));
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $db;
}

?>
