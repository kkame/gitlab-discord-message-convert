<?php


$webhook = substr($_SERVER['REQUEST_URI'], 1);


$payload = file_get_contents('php://input');

if(empty($payload))
    throw new Exception("Empty payload");

$payloadObject = json_decode($payload);


//If the channel name is url pattern
if (isset($payloadObject->channel) && filter_var($payloadObject->channel, FILTER_VALIDATE_URL)) {
    $webhook = $payloadObject->channel;
    unset($payloadObject->channel);
}

//If attachments are empty, they are considered to be the wrong pattern
if (empty($payloadObject->attachments)) {


    $attachment = (object)[
        'text' => $payloadObject->text,
    ];
    $payloadObject->attachments = [$attachment];

    unset($payloadObject->fallback);
    unset($payloadObject->text);
    $payload = json_encode($payloadObject);
}


$ch = curl_init($webhook);

// Set request method to POST
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    curl_setopt($ch, CURLOPT_POST, 1);
}
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, getallheaders());
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, 1);

$response = curl_exec($ch);
curl_close($ch);


list($header, $body) = explode("\r\n\r\n", $response, 2);

$data = explode("\n", $header);
$response_status_code = $data[0];
http_response_code($response_status_code);


array_shift($data);

foreach ($data as $part) {
    header($part);
}


echo $body;
