<?php

require ('conn.php');
require __DIR__ . '/vendor/autoload.php';

use \Curl\Curl;
$conf = new \stdClass();
$curl = new Curl();
$curl->setHeader('Content-Type','application/x-www-form-urlencoded');

    $response = $curl->post('https://discord.com/api/oauth2/token', 
        array(
            'grant_type' => 'authorization_code',
            'client_id' => '[client id]',
            'client_secret' => '[client secret]',
            'redirect_uri' => 'http://localhost:80/login.php',
            'code' => $_GET["code"],
            'scope' => 'identify guilds'
        )
    );


$resposta = json_decode(json_encode($curl->response),TRUE);
echo "<pre>";
print_r($resposta);
$dados = array('token_acesso' => $resposta['access_token'],
                'expira_em' => $resposta['expires_in'],
                'token_refresh' => $resposta['refresh_token'],
                'webhook_id' => $resposta['webhook']['id'],
                'webhook_name' => $resposta['webhook']['name'],
                'webhook_token' => $resposta['webhook']['token'],
                'webhook_endpoint_url' => $resposta['webhook']['url'],
                'canal_id' => $resposta['webhook']['channel_id']);


$names_str = implode(" , ",$dados);
$parts = explode (" , ",$names_str);

$stmt = $conn->prepare("SELECT canal FROM webhook_dados WHERE canal='$parts[7]'");
$stmt->execute(['$parts[7]']); 
$canal = $stmt->fetch();
if ($canal) {
    $canal=null;
    $conn=null;
    
    
} else {
print_r($canal);
$sql = "INSERT INTO webhook_dados(token_acesso,expire,token_refresh,webhook_id,webhook_name,webhook_token,url_endpoint,canal)VALUES (
        '$parts[0]', '$parts[1]','$parts[2]','$parts[3]','$parts[4]','$parts[5]','$parts[6]','$parts[7]')";
$conn->query($sql);
} 

