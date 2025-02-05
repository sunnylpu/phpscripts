<?php
// Check if the IP is from shared internet
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
}
// Check if the IP is from a proxy
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
// If not from shared internet or proxy, consider it as the remote address
else {
    $ip_address = $_SERVER['REMOTE_ADDR'];
}

// Display the determined IP address
echo $ip_address;
?>
