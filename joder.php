<?php
$log = date('Y-m-d H:i:s').' - Request: '.$_SERVER['REQUEST_METHOD']."\n";
file_put_contents('log.txt', $log, FILE_APPEND);

$url = 'https://star.api.edge.bamgrid.com/graph/v1/device/graphql';
$auth_refreshtoken = file_get_contents('auth_refreshtoken.txt');

$ch = curl_init();
$headers = [
    'authority: star.api.edge.bamgrid.com',
    'accept: application/json',
    'accept-language: es-AR,es;q=0.9,en;q=0.8,en-US;q=0.7,uk;q=0.6',
    'authorization: c3RhciZicm93c2VyJjEuMC4w.COknIGCR7I6N0M5PGnlcdbESHGkNv7POwhFNL-_vIdg', // "star&browser&1.0.0"
    'content-type: application/json',
    'dnt: 1',
    'origin: https://www.starplus.com',
    'referer: https://www.starplus.com/',
    'sec-ch-ua: "Chromium";v="110", "Not A(Brand";v="24", "Google Chrome";v="110"',
    'sec-ch-ua-mobile: ?0',
    'sec-ch-ua-platform: "Windows"',
    'sec-fetch-dest: empty',
    'sec-fetch-mode: cors',
    'sec-fetch-site: cross-site',
    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36',
    'x-application-version: 1.0.0',
    'x-bamsdk-client-id: star-22bcaf0a',
    'x-bamsdk-platform: javascript/windows/chrome',
    'x-bamsdk-platform-id: browser',
    'x-bamsdk-version: 20.0',
    'x-dss-edge-accept: vnd.dss.edge+json; version=1',
];
$payload = '{"query":"mutation refreshToken($input: RefreshTokenInput!) { refreshToken(refreshToken: $input) { activeSession { sessionId } } }","operationName":"refreshToken","variables":{"input":{"refreshToken":"'.$auth_refreshtoken.'"}}}';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$json = curl_exec($ch);
curl_close($ch);
$json = json_decode($json);

file_put_contents('auth_return.txt', json_encode($json, JSON_PRETTY_PRINT));

if(!isset($json->extensions) || !isset($json->extensions->sdk->token)) die('Error');

$tokens = $json->extensions->sdk->token;
file_put_contents('auth_accesstoken.txt', $tokens->accessToken);
file_put_contents('auth_refreshtoken.txt', $tokens->refreshToken);


$payload = file_get_contents('php://input');
$headers = [
    "authorization: Bearer ".$tokens->accessToken,
    "content-type: application/json",
    "origin: https://www.starplus.com",
    "referer: https://www.starplus.com/",
    "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36",
    'x-application-version: 1.0.0',
    'x-bamsdk-client-id: star-22bcaf0a',
    'x-bamsdk-platform: javascript/windows/chrome',
    'x-bamsdk-platform-id: browser',
    'x-bamsdk-version: 20.0',
    'x-dss-edge-accept: vnd.dss.edge+json; version=1',
];

$ch = curl_init();
$url = 'https://star.playback.edge.bamgrid.com/widevine/v1/obtain-license';
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$auth = curl_exec($ch);
curl_close($ch);

header('Access-Control-Allow-Origin: *');
echo $auth;
