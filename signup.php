<?php

require_once 'common.php';
require_once 'securimage/securimage.php';

// Code Validation
if ($_REQUEST['email'] == null) {
	header('Location: /');
	exit;
}

$image = new Securimage();
if ($image->check($_REQUEST['captcha_code']) == true) {
    $db = database();
    $stmt = $db->prepare('select * from signup where email = :email');
    $stmt->execute(array(
        'email' => $_REQUEST['email']
    ));

    $existing = $stmt->rowCount();
    if(empty($existing)) {
        $stmt = $db->prepare('insert into signup set org = :org, title = :title, name = :name,
          email = :email, service = :service');
        $stmt->execute(array(
            'title' => $_REQUEST['title'],
            'name' => $_REQUEST['name'],
            'org' => $_REQUEST['org'],
            'email' => $_REQUEST['email'],
            'service' => $_REQUEST['service']
        ));
        header('Location: /thanks');
        exit;
    } else {
        header('Location: /registered');
        exit;
    }
} else {
    header('Location: /#signup');
    exit;
}







