<?php
$pub_key = "/tmp/pub_key";
$master_key = "/tmp/master_key";

$plain = $_POST['plain'];
$access_policy = $_POST['access_policy'];

$hash_id = hash("md5", $plain);

$plain_file = "/tmp/plain_" . $hash_id;
$cipher_file = "/tmp/cipher_" . $hash_id;

if (file_exists($cipher_file)) {
    $json['status'] = 'success';
    $cipher = file_get_contents($cipher_file);
    $json['cipher'] = base64_encode($cipher);
    $json['hash'] = $hash_id;
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
    return;
} 

$file = fopen($plain_file, "w");

if ($file == false) {
    $json['status'] = 'fail';
    $json['err'] = 'open plain file fail';
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
    return;
}

fwrite($file, $plain);
fclose($file);

$output = null;
$retval = null;
$cmd = "cpabe-enc -k -o " . $cipher_file . " " . $pub_key . " " . $plain_file . " \"" . $access_policy . "\"";

error_log($cmd);
exec($cmd . " 2>&1", $output, $retval);

if ($retval == 0) {
    $json['status'] = 'success';
    $cipher = file_get_contents($cipher_file);
    $json['cipher'] = base64_encode($cipher);
    $json['hash'] = $hash_id;
} else {
    $json['status'] = 'fail';
    $json['err'] = implode("\n", $output);
}

echo json_encode($json, JSON_UNESCAPED_UNICODE);
