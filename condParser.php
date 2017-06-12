<?php

function checkSyntax($condition) {
    $parts = explode(" ", $condition);

    $action = strtolower($parts[0]);
    $key = $parts[1];
    $value = $parts[2];

    $in = in_array($action, array(
        "has",
        "comp"
    ));

    if(!$in) error(6, "unknown action " . $action);

    if(strlen($key) == 0) error(6, "key empty");

    if($action == "comp") {
        $op = $value[0];
        $value = substr($value, 1);
        $in = in_array($op, array("=", "<", ">"));
        if(!$in) error(6, "unknown operation " . $op);

        if(($op == "<" || $op == ">") && !is_numeric($value)) error(6, "need numeric not " . $value);
    }
}

function getSQLCondition($conn, $condition) {
    $key = explode(" ", $condition)[1];
    if($conn != null)
        $key = mysqli_real_escape_string($conn, $key);
    return "`key`='$key'";
}

function passesCondition($meta, $condition) {
    $parts = explode(" ", $condition);

    $action = strtolower($parts[0]);
    $key = $parts[1];
    $value = $parts[2];

    if(!isset($meta[$key])) return false;

    if($action == "has") return true;

    $metaValue = $meta[$key];

    if($action == "comp") {
        $op = $value[0];
        $value = substr($value, 1);

        if($op == "=") return $value == $metaValue;

        if(!is_numeric($metaValue)) return false;

        $metaValue = floatval($metaValue);
        $value = floatval($value);

        if($op == ">") return $metaValue > $value;
        if($op == "<") return $metaValue < $value;
    }

    return false;
}

function longOr($sqlList) {
    $ret = "";

    for($i = 0; $i < count($sqlList); $i++) {
        if(empty($sqlList[$i])) {
            array_splice($sqlList, $i, 1);
            $i--;
        }
    }

    if(count($sqlList) == 0) return "";

    $ret = $sqlList[0];
    for($i = 1; $i < count($sqlList); $i++) {
        $ret = $ret . " or " . $sqlList[$i];
    }

    return "(" . $ret . ")";
}

function longAnd($sqlList) {
    $ret = "";

    for($i = 0; $i < count($sqlList); $i++) {
        if(empty($sqlList[$i])) {
            array_splice($sqlList, $i, 1);
            $i--;
        }
    }

    if(count($sqlList) == 0) return "";

    $ret = $sqlList[0];
    for($i = 1; $i < count($sqlList); $i++) {
        $ret = $ret . " and " . $sqlList[$i];
    }

    return "(" . $ret . ")";
}