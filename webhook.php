<?php
include ('conn.php');

$select = $conn->prepare("SELECT webhook_id, webhook_token, token_refresh,expire FROM webhook_dados");
$select->execute(array());
foreach($select as $row) {
    echo $row['webhook_id'];
    echo $row['webhook_token'];
    echo $row['token_refresh'];
    echo $row['expire'];
    
$json = json_decode(file_get_contents("php://input"), true);
$data = json_encode($json);
$endpoint = curl_init('https://discord.com/api/webhooks/'.$row['webhook_id'].'/'.$row['webhook_token']);
curl_setopt_array($endpoint, array(
    CURLOPT_POST => TRUE,
    CURLOPT_HTTPHEADER =>array('Content-Type:application/json'),
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_POSTFIELDS => $data
));
$response = curl_exec($endpoint);
}
