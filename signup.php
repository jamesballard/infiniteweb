<?php

require_once 'common.php';

if ($_REQUEST['email'] == null) {
	header('Location: /');
	exit;
}

$db = database();
$stmt = $db->prepare('select * from signup where email = :email');
$stmt->execute(array(
    'email' => $_REQUEST['email']
));

$existing = $stmt->rowCount();
if(empty($existing)) {
    $stmt = $db->prepare('insert into signup set org = :org, title = :title, name = :name, email = :email');
    $stmt->execute(array(
        'title' => $_REQUEST['title'],
        'name' => $_REQUEST['name'],
        'org' => $_REQUEST['org'],
        'email' => $_REQUEST['email']
    ));
    header('Location: /thanks');
    exit;
} else {
    header('Location: /registered');
    exit;
}





