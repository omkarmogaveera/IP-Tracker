<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../classes/User.php';
include_once '../../classes/Tracker.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$tracker = new Tracker($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->username_or_email) && !empty($data->password)){
    
    $exact_lat = isset($data->exact_lat) ? $data->exact_lat : null;
    $exact_lon = isset($data->exact_lon) ? $data->exact_lon : null;

    // Attempt login
    if($user->login($data->username_or_email, $data->password)){
        
        // Log successful attempt
        $tracker->logAttempt($user->id, 'success', $exact_lat, $exact_lon);

        http_response_code(200);
        echo json_encode(array(
            "status" => "success",
            "message" => "Login successful.",
            "user" => array(
                "id" => $user->id,
                "username" => $user->username,
                "email" => $user->email
            )
        ));
    } else {
        // Log failed attempt, if user exists we might know their ID, otherwise log as NULL
        // We'd need to modify login to set user ID even on failure if we wanted that, 
        // but for security it's better to just log failed attempts generally or by IP.
        // We will pass NULL for user_id on complete failure.
        $tracker->logAttempt(null, 'failed', $exact_lat, $exact_lon);

        http_response_code(401);
        echo json_encode(array("status" => "error", "message" => "Login failed. Incorrect credentials."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Incomplete login data."));
}
?>
