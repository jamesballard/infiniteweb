<?php

class GoogleRecaptcha {
    /* Google recaptcha API url */
    private $google_url = "https://www.google.com/recaptcha/api/siteverify";
    private $secret = '6LdbWQETAAAAABF378Phhpu4Zmi0OTSthcdBETpS';

    public function VerifyCaptcha($response) {
        $url = $this->google_url."?secret=".$this->secret.
            "&response=".$response;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        $curlData = curl_exec($curl);

        curl_close($curl);

        $res = json_decode($curlData, TRUE);
        if($res['success'] == 'true')
            return TRUE;
        else
            return FALSE;
    }
}

require_once 'common.php';

// Code Validation
if ($_REQUEST['email'] == null) {
	header('Location: /');
	exit;
}

$response = $_REQUEST['g-recaptcha-response'];

if(!empty($response)) {
    $cap = new GoogleRecaptcha();
    $verified = $cap->VerifyCaptcha($response);

    if($verified) {
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
            $to      = 'support@infiniterooms.co.uk';
            $subject = 'New Signup: '.$_REQUEST['service'];
            $message = $_REQUEST['title'].' '.$_REQUEST['name'].' ('.$_REQUEST['email'].') from '.$_REQUEST['org'];
            $headers = 'From: webmaster@infiniterooms.co.uk' . "\r\n" .
                'Reply-To: webmaster@infiniterooms.co.uk' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
            mail($to, $subject, $message, $headers);
            header('Location: /thanks');
            exit;
        } else {
            header('Location: /registered');
            exit;
        }
    } else {
        header('Location: /robotp send emai');
    }
}








