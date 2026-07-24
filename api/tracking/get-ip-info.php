<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once '../../classes/Tracker.php';

$tracker = new Tracker(); // No DB connection needed just for fetching IP info

$ip_address = $tracker->getUserIP();
$geoData = $tracker->getGeoLocation($ip_address);

if($geoData) {
    // If the local Tracker replaced localhost with a public IP, use that public IP in the response
    $returnedIp = isset($geoData['query']) ? $geoData['query'] : $ip_address;
    
    http_response_code(200);
    echo json_encode(array(
        "status" => "success",
        "ip" => $returnedIp,
        "geo" => $geoData
    ));
} else {
    http_response_code(500);
    echo json_encode(array(
        "status" => "error", 
        "message" => "Could not retrieve IP information.",
        "ip" => $ip_address
    ));
}
?>
