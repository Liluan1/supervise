<?php
$pub_key = "/tmp/pub_key";
$hash_plain = $_POST['hash'];
$key = $_POST['key'];
$hash_key = hash("md5", $key);

error_log("Hash ID: " . $hash_plain);
error_log("Key: " . $key);

$plain_file = "/tmp/plain_" . $hash_plain;
$cipher_file = "/tmp/cipher_" . $hash_plain;

if (!file_exists($cipher_file)) {
    $cipher = $_POST['cipher'];
    $file = fopen($cipher_file, "w");

    if ($file == false) {
        $json['status'] = 'fail';
        $json['err'] = 'open cipher file fail';
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        return;
    }

    fwrite($file, base64_decode($cipher));
    fclose($file);
}

if (file_exists($plain_file)) {
    $json['status'] = 'success';
    $plain = file_get_contents($plain_file);
    $json['plain'] = $plain;
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
    return;
}

$priv_key = "/tmp/key_" . $hash_key;
if (!file_exists($priv_key)){
    $file = fopen($priv_key, "w");
    
    if ($file == false) {
        $json['status'] = 'fail';
        $json['err'] = 'open key file fail';
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        return;
    }
    
    fwrite($file, base64_decode($key));
    fclose($file);
}

$output = null;
$retval = null;

$start_time = microtime(true);
$cmd = "cpabe-dec -o " . $plain_file . " " . $pub_key . " " . $priv_key . " " . $cipher_file;
$end_time = microtime(true);
$run_time = ($end_time - $start_time) * 1000;

error_log($cmd);
error_log("Decrypt time: " . $run_time);

exec($cmd . " 2>&1", $output, $retval);

if ($retval == 0) {
    $json['status'] = 'success';
    $plain = file_get_contents($plain_file);
    $json['plain'] = $plain;
    $json['time'] = $run_time;
} else {
    $json['status'] = 'fail';
    $json['err'] = implode("\n", $output);
    $json['time'] = $run_time;
}

echo json_encode($json, JSON_UNESCAPED_UNICODE);
