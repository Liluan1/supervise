<?php
$pub_key = "/tmp/pub_key";
$master_key = "/tmp/master_key";

$plain = $_POST['plain'];
$access_policy = $_POST['access_policy'];

$hash_plain = hash("md5", $plain);

$plain_file = "/tmp/plain_" . $hash_plain;
$cipher_file = "/tmp/cipher_" . $hash_plain;

if (file_exists($cipher_file)) {
    $json['status'] = 'success';
    $cipher = file_get_contents($cipher_file);
    $json['cipher'] = base64_encode($cipher);
    $json['hash'] = $hash_plain;
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

$start_time = microtime(true);
// error_log($start_time);
$cmd = "cpabe-enc -o " . $cipher_file . " " . $pub_key . " " . $plain_file . " \"" . $access_policy . "\"";
$end_time = microtime(true);
// error_log($end_time);
$run_time = ($end_time - $start_time) * 1000;

error_log($cmd);
error_log("Encrypt time: " . $run_time);

exec($cmd . " 2>&1", $output, $retval);

if ($retval == 0) {
    $json['status'] = 'success';
    $cipher = file_get_contents($cipher_file);
    $json['cipher'] = base64_encode($cipher);
    $json['hash'] = $hash_plain;
    $json['time'] = $run_time;
} else {
    $json['status'] = 'fail';
    $json['err'] = implode("\n", $output);
    $json['time'] = $run_time;
}

echo json_encode($json, JSON_UNESCAPED_UNICODE);
