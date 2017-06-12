<?php

require("utils.php");

$dataString = $_POST["data"];
$file = $_FILES["file"];
$mimeType = (isset($_REQUEST["mimeType"])) ? $_REQUEST["mimeType"] : $file["type"];

if(!isset($file)) error(1, "missing file");
if(!isset($dataString)) error(2, "missing data");

echo json_encode(array(
    "result" => "success",
    "file" => file_get_contents($file["tmp_name"]),
    "data" => json_decode($dataString),
    "type" => $mimeType
));