<?php
#database credentials
include('conn.php');
require __DIR__ . '/vendor/autoload.php';
use \Curl\Curl;

$select = $conn->prepare("SELECT webhook_id, webhook_token, token_refresh,expires_in,canal,servidor FROM webhook_dados");
$select->execute(array());


foreach ($select as $row) {
    //verifica o tempo de expiração do token, se estiver passado ele renova
    if (time() > $row['expires_in']) {
        $curl = new Curl();
        $curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');

        $response = $curl->post(
            'https://discord.com/api/oauth2/token',
            array(
            'grant_type' => 'refresh_token',
            'client_id' => 'BOT CLIENT ID',
            'client_secret' => 'BOT CLIENT SECRET',
            'redirect_uri' => 'http://localhost:80/webhook.php',
            'refresh_token'=> $row['token_refresh'],
        )
        );
        print_r($row['token_refresh']);
        print_r($response);
        $refresh_new_access = json_decode(json_encode($curl->response->access_token), true);
        $refresh_new_expires = json_decode(json_encode($curl->response->expires_in), true);
        $new_refresh_token = json_decode(json_encode($curl->response->refresh_token), true);
        $new_time =  time()+$refresh_new_expires;
        $sql = "UPDATE webhook_dados SET token_acesso='$refresh_new_access',token_refresh='$new_refresh_token',expires_in='$new_time' WHERE servidor=".$row['servidor'];
        $conn->query($sql);
    }//fim get new token
    
    //inicio post
    $json = json_decode(file_get_contents("php://input"), true);
    $data = json_encode($json);
    $endpoint = curl_init('https://discord.com/api/webhooks/'.$row['webhook_id'].'/'.$row['webhook_token']);
    curl_setopt_array($endpoint, array(
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER =>array('Content-Type:application/json'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => $data,
    
            ));

    $response = curl_exec($endpoint);
    $a = json_decode($response, true);

    if ($a["code"] == '10015') {
        echo $a["code"];
        $sql = "DELETE FROM webhook_dados WHERE webhook_id=".$row['webhook_id'];
        $conn->query($sql);
    }
}