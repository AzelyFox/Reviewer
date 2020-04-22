<?php

declare(strict_types = 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST');
header('Content-Type: text/html; charset=UTF-8');

require_once "constants.php";

# connects to local database
$DB = mysqli_connect($DB_HOST, $DB_ID, $DB_PW, $DB_NAME);
if (mysqli_connect_errno()) {
    $output = array();
    $output["result"] = -2;
    $output["error"] = "DB CONNECTION FAILURE : ".mysqli_connect_error();
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

function validateKey(Mysqli $DB, string $key) {
    $_validation = array();
    $_validation["service_index"] = 0;
    $_validation["service_id"] = "";

    # execute key validate query
    try {
        $DB_SQL_KEY = "SELECT `service_index`, `service_id` FROM `reviewer_service` WHERE `service_key` = ?";
        $DB_STMT_KEY = $DB->prepare($DB_SQL_KEY);
        # database query not ready
        if (!$DB_STMT_KEY) {
            $output = array();
            $output["result"] = -2;
            $output["error"] = "DB PREPARE FAILURE";
            $output["error_debug"] = basename(__FILE__).".".__LINE__;
            $outputJson = json_encode($output);
            echo urldecode($outputJson);
            exit();
        }
        $DB_STMT_KEY->bind_param("s", $key);
        $DB_STMT_KEY->execute();
        if ($DB_STMT_KEY->errno != 0) {
            # key query error
            $output = array();
            $output["result"] = -2;
            $output["error"] = $DB_STMT_KEY->error;
            $output["error_debug"] = basename(__FILE__).".".__LINE__;
            $outputJson = json_encode($output);
            echo urldecode($outputJson);
            exit();
        }
        $DB_STMT_KEY->bind_result($_validation["service_index"], $_validation["service_id"]);
        $DB_STMT_KEY->store_result();
        if ($DB_STMT_KEY->num_rows != 1) {
            # key is not in database
            $output = array();
            $output["result"] = -3;
            $output["error"] = "KEY NOT VALID";
            $output["error_debug"] = basename(__FILE__).".".__LINE__;
            $outputJson = json_encode($output);
            echo urldecode($outputJson);
            exit();
        }
        $DB_STMT_KEY->fetch();
        $DB_STMT_KEY->close();
    } catch (Exception $e) {
        # key query error
        $output = array();
        $output["result"] = -2;
        $output["error"] = $e->getMessage();
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }

    # execute service expire set query
    try {
        $DB_SQL_KEY = "UPDATE `reviewer_service` SET `service_expire` = DATE_ADD(NOW(), INTERVAL 90 DAY) WHERE `service_index` = ?";
        $DB_STMT_KEY = $DB->prepare($DB_SQL_KEY);
        # database query not ready
        if (!$DB_STMT_KEY) {
            $output = array();
            $output["result"] = -2;
            $output["error"] = "DB PREPARE FAILURE";
            $output["error_debug"] = basename(__FILE__).".".__LINE__;
            $outputJson = json_encode($output);
            echo urldecode($outputJson);
            exit();
        }
        $DB_STMT_KEY->bind_param("s", $key);
        $DB_STMT_KEY->execute();
        if ($DB_STMT_KEY->errno != 0) {
            # service expire set query error
            $output = array();
            $output["result"] = -2;
            $output["error"] = $DB_STMT_KEY->error;
            $output["error_debug"] = basename(__FILE__).".".__LINE__;
            $outputJson = json_encode($output);
            echo urldecode($outputJson);
            exit();
        }
        $DB_STMT_KEY->close();
    } catch (Exception $e) {
        # service expire set query error
        $output = array();
        $output["result"] = -2;
        $output["error"] = $e->getMessage();
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }

    return $_validation;
}

function increaseCount(Mysqli $DB, $validation) {
    # execute count increase query
    try {
        $DB_SQL_KEY = "UPDATE `reviewer_service` SET `service_called` = `service_called` + 1 WHERE `service_index` = ?";
        $DB_STMT_KEY = $DB->prepare($DB_SQL_KEY);
        # database query not ready
        if (!$DB_STMT_KEY) {
            $output = array();
            $output["result"] = -2;
            $output["error"] = "DB PREPARE FAILURE";
            $output["error_debug"] = basename(__FILE__).".".__LINE__;
            $outputJson = json_encode($output);
            echo urldecode($outputJson);
            exit();
        }
        $DB_STMT_KEY->bind_param("i", $validation["service_index"]);
        $DB_STMT_KEY->execute();
        if ($DB_STMT_KEY->errno != 0) {
            # count query error
            $output = array();
            $output["result"] = -2;
            $output["error"] = $DB_STMT_KEY->error;
            $output["error_debug"] = basename(__FILE__).".".__LINE__;
            $outputJson = json_encode($output);
            echo urldecode($outputJson);
            exit();
        }
        $DB_STMT_KEY->close();
    } catch (Exception $e) {
        # count query error
        $output = array();
        $output["result"] = -2;
        $output["error"] = $e->getMessage();
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
}

function sendEmail($USER_EMAILS, $MESSAGE) {

}

function sendFCM($USER_UUIDS, $MESSAGE) {

}

?>