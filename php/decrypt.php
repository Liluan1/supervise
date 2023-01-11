<?php
$pub_key = "/tmp/pub_key";
$hash_id = $_POST['hash'];
$key = $_POST['key'];

$plain_file = "/tmp/plain_" . $hash_id;
$cipher_file = "/tmp/cipher_" . $hash_id;

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

$priv_key = "/tmp/key_" . $hash_id;
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
$cmd = "cpabe-dec -o " . $plain_file . " " . $pub_key . " " . $priv_key . " " . $cipher_file;

error_log($cmd);
exec($cmd . " 2>&1", $output, $retval);

if ($retval == 0) {
    $json['status'] = 'success';
    $plain = file_get_contents($plain_file);
    $json['plain'] = $plain;
} else {
    $json['status'] = 'fail';
    $json['err'] = implode("\n", $output);
}

echo json_encode($json, JSON_UNESCAPED_UNICODE);
