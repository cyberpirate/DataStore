<?php

require("utils.php");

$dataString = $_POST["data"];
$file = $_FILES["file"];

if(!isset($file)) error(1, "missing file");
if(!isset($dataString)) error(2, "missing data");

$data = json_decode($dataString, true);

if($data == null) error(3, "can't parse json");

$dataKeys = array_keys($data);
for($i = 0; $i < count($dataKeys); $i++) {
    $key = $dataKeys[$i];
    $value = $data[$key];

    if(gettype($value) == "object") error(3, $key . " can't be an object");
    if(gettype($value) == "array") error(3, $key . " can't be an array");
}

$mimeType = (isset($_REQUEST["mimeType"])) ? $_REQUEST["mimeType"] : $file["type"];

$conn = mysqli_connect(SQL_NAME, SQL_USER, SQL_PASS, SQL_DB);

if(!$conn) error(4, "Sql connection error: " . mysqli_connect_error());

$mimeType = mysqli_real_escape_string($conn, $mimeType);
$size = filesize($file["tmp_name"]);
$size = $size ? $size : 0;
$sql = "insert into `files` (`mimeType`, `size`) values ('$mimeType', '$size')";

$ret = mysqli_query($conn, $sql);

$imageId = mysqli_insert_id($conn);

move_uploaded_file($file["tmp_name"], "file/" . $imageId);

for($i = 0; $i < count($dataKeys); $i++) {
    $key = mysqli_real_escape_string($conn, strtolower($dataKeys[$i]));
    $value = mysqli_real_escape_string($conn, $data[$key]);

    $sql = "insert into `meta` (`imageId`, `key`, `value`) values ('$imageId', '$key', '$value')";
    $ret = mysqli_query($conn, $sql);
}

mysqli_close($conn);

$ret = array(
    "result" => "success",
    "imageId" => $imageId
);
echo json_encode($ret);