<?php

define("SQL_USER", "data_store_user");
define("SQL_PASS", "");
define("SQL_NAME", "localhost");
define("SQL_DB", "data_store");


function error($code, $msg) {
    $ret = array(
        "result" => "error",
        "code" => $code,
        "msg" => $msg
    );
    echo json_encode($ret);
    exit();
}