<?php

require("utils.php");
require("condParser.php");

//set up variables
$dataString = file_get_contents('php://input');

$data = json_decode($dataString, true);

if($data == null) error(3, "can't parse json");

$limit = is_numeric($data["limit"]) ? intval($data["limit"]) : 1000;
$metaIdMax = is_numeric($data["metaIdMax"]) ? intval($data["metaIdMax"]) : 0;

if(!isset($data["conditions"]) || count($data["conditions"]) == 0) error(5, "conditions missing");

//connect to database
$conn = mysqli_connect(SQL_NAME, SQL_USER, SQL_PASS, SQL_DB);
if(!$conn) error(4, "Sql connection error: " . mysqli_connect_error());

//check syntax
$conditions = $data["conditions"];
$sqlConditions = $conditions;
for($i = 0; $i < count($sqlConditions); $i++) {
    $cond = $sqlConditions[$i];
    checkSyntax($cond);
    $sqlConditions[$i] = getSQLCondition($conn, $cond);
}

//get ids
$sqlConditions = longOr($sqlConditions);
$sql = "select distinct `imageId` from `meta` where $sqlConditions and `id`>'$metaIdMax' limit $limit";
$result = mysqli_query($conn, $sql);

$imageIds = array();
if (mysqli_num_rows($result) > 0) {
    // output data of each row
    while($row = mysqli_fetch_assoc($result)) {
        $imageIds[] = "`imageId`='" . $row["imageId"] . "'";
    }
} else {
    error(7, "no results");
}

//get meta tags for ids
$sqlIdList = longOr($imageIds);
$sql = "select * from `meta` where $sqlIdList and $sqlConditions";
$result = mysqli_query($conn, $sql);

$metaIdMax = 0;

$imageInfo = array();
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $imageInfo[$row["imageId"]][$row["key"]] = $row["value"];
        $metaIdMax = max($metaIdMax, intval($row["id"]));
    }
} else {
    error(7, "no results");
}

//validate ids pass conditions
$passedIds = array();
$keys = array_keys($imageInfo);
for($i = 0; $i < count($keys); $i++) {
    $passes = true;
    $image = $imageInfo[$keys[$i]];
    for($j = 0; $j < count($conditions) && $passes; $j++) {
        $passes = $passes && passesCondition($image, $conditions[$j]);
    }
    if($passes) $passedIds[] = $keys[$i];
}

if(empty($passedIds)) error(7, "no results");

//return results
echo json_encode(array(
    "result" => "success",
    "imageIds" => $passedIds,
    "metaIdMax" => $metaIdMax
));

mysqli_close($conn);