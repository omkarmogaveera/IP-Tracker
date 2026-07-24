<?php
/**
 * Tracker Class
 * Handles IP tracking, geo-location fetching, and logging
 */
class Tracker {
    private $conn;
    private $table_name = "login_logs";

    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        }
    }

    /**
     * Get the real IP address of the user
     */
    public function getUserIP() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
            
        // If there are multiple IPs (like from a proxy), take the first one
        if (strpos($ipaddress, ',') !== false) {
            $ips = explode(',', $ipaddress);
            $ipaddress = trim($ips[0]);
        }
        
        return $ipaddress;
    }

    /**
     * Fetch Geo Location data from an external API (ip-api.com)
     */
    public function getGeoLocation($ip) {
        // For local development, if IP is localhost, let's fetch the actual public IP so you can test it!
        if ($ip == '127.0.0.1' || $ip == '::1') {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.ipify.org');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // bypass local SSL issues
            $publicIp = curl_exec($ch);
            curl_close($ch);
            
            if ($publicIp) {
                $ip = $publicIp;
            } else {
                return [
                    'status' => 'success',
                    'country' => 'Localhost',
                    'city' => 'Localhost',
                    'lat' => 0,
                    'lon' => 0
                ];
            }
        }

        $apiUrl = "http://ip-api.com/json/{$ip}";
        
        // Initialize cURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 seconds timeout
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            return json_decode($response, true);
        }

        return null;
    }

    /**
     * Log the login attempt
     */
    public function logAttempt($user_id, $status, $exact_lat = null, $exact_lon = null, $exact_address = null) {
        if (!$this->conn) return false;

        $ip_address = $this->getUserIP();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'UNKNOWN';
        
        $geoData = $this->getGeoLocation($ip_address);
        
        $country = null;
        $city = null;
        $latitude = null;
        $longitude = null;

        if ($geoData && isset($geoData['status']) && $geoData['status'] == 'success') {
            $country = $geoData['country'] ?? null;
            $city = $geoData['city'] ?? null;
            $latitude = $geoData['lat'] ?? null;
            $longitude = $geoData['lon'] ?? null;
        }

        // Override with exact GPS coordinates if provided by the frontend
        if ($exact_lat !== null && $exact_lon !== null) {
            $latitude = $exact_lat;
            $longitude = $exact_lon;
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, ip_address, country, city, latitude, longitude, exact_address, user_agent, status) 
                  VALUES (:user_id, :ip_address, :country, :city, :latitude, :longitude, :exact_address, :user_agent, :status)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':ip_address', $ip_address);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':city', $city);
        $stmt->bindParam(':latitude', $latitude);
        $stmt->bindParam(':longitude', $longitude);
        $stmt->bindParam(':exact_address', $exact_address);
        $stmt->bindParam(':user_agent', $user_agent);
        $stmt->bindParam(':status', $status);

        return $stmt->execute();
    }
}
?>
