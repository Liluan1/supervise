<?php
$user = $_POST['user'];
error_log("user: " . $user);
$attr = $_POST['attr'];
error_log("attr: " . $attr);
$key_name = hash('md5', $user);
$key_file = "/tmp/key_" . $key_name;
$pub_key = "/tmp/pub_key";
$master_key = "/tmp/master_key";

$output = null;
$retval = null;

$cmd = "cpabe-keygen -o " . $key_file . " " . $pub_key . " " . $master_key . " " . $attr;
exec($cmd . " 2>&1", $output, $retval);

if ($retval == 0) {
    $json['status'] = 'success';
    $key = file_get_contents($key_file);
    $json['key'] = base64_encode($key);
    unlink($key_file);
} else {
    $json['status'] = 'fail';
    $json['err'] = implode("\n", $output);
}

echo json_encode($json, JSON_UNESCAPED_UNICODE);
unlink($key_file);