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
require_once 'mandrill/src/Mandrill.php';
$mandrill = new Mandrill('tXUxwxWlFGL6wT6zEg7HuQ');

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
            $body = $_REQUEST['title'].' '.$_REQUEST['name'].' ('.$_REQUEST['email'].') from '.$_REQUEST['org'];
            try {
                $message = array(
                    'html' => $body,
                    'text' => $body,
                    'subject' => $subject,
                    'from_email' => 'support@infiniterooms.co.uk',
                    'from_name' => 'Infinite Rooms Signup',
                    'to' => array(
                        array(
                            'email' => $to,
                            'name' => $_REQUEST['name'],
                            'type' => 'to'
                        )
                    ),
                    'headers' => array('Reply-To' => 'support@infiniterooms.co.uk'),
                    'tags' => array('new-signup')
                );
                $result = $mandrill->messages->send($message);
            } catch(Mandrill_Error $e) {
                // Mandrill errors are thrown as exceptions
                error_log('A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage());
            }

            $to      = $_REQUEST['email'];
            $subject = 'Infinite Rooms: Thanks for registering';
            $html = '<p>Hi '.$_REQUEST['name'].'</p>';
            $html .= '<p>Thank you for registering to <a href="https://infiniterooms.co.uk">Infinite Rooms</a>.';
            $html .= 'We are currently setting up your account details and will contact you shortly.</p>';
            $html .= '<p>Yours</p><p>James</p>';

            $text = 'Hi '.$_REQUEST['name'].chr(10);
            $text .= 'Thank you for registering to Infinite Rooms.';
            $text .= 'We are currently setting up your account details and will contact you shortly.'.chr(10);
            $text .= 'Yours'.chr(10).'James';
            try {
                $message = array(
                    'html' => $html,
                    'text' => $text,
                    'subject' => $subject,
                    'from_email' => 'support@infiniterooms.co.uk',
                    'from_name' => 'James Ballard',
                    'to' => array(
                        array(
                            'email' => $to,
                            'name' => $_REQUEST['name'],
                            'type' => 'to'
                        )
                    ),
                    'headers' => array('Reply-To' => 'support@infiniterooms.co.uk'),
                    'tags' => array('new-registration')
                );
                $result = $mandrill->messages->send($message);
            } catch(Mandrill_Error $e) {
                // Mandrill errors are thrown as exceptions
                error_log('A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage());
            }
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








