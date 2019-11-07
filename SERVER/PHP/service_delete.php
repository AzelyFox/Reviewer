<?php

require_once "./inc.php";

# initialize service id
if (isset($_REQUEST["service_id"]))
{
    $service_id = $_REQUEST["service_id"];
    if (mb_strlen($service_id) < 4 || mb_strlen($service_id) > 20) {
        $output = array();
        $output["result"] = -1;
        $output["error"] = "service_id IS NOT VALID";
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
} else {
    $output = array();
    $output["result"] = -1;
    $output["error"] = "service_id IS EMPTY";
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

# initialize service pw
if (isset($_REQUEST["service_pw"]))
{
    $service_pw = $_REQUEST["service_pw"];
    if (mb_strlen($service_pw) < 4 || mb_strlen($service_pw) > 20) {
        $output = array();
        $output["result"] = -1;
        $output["error"] = "service_pw IS NOT VALID";
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
} else {
    $output = array();
    $output["result"] = -1;
    $output["error"] = "service_pw IS EMPTY";
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

# execute service info query
try {
    $DB_SQL = "SELECT `service_index`, `service_email`, `service_pw`, `service_called`, `service_key`, `service_created`, `service_expire` FROM `reviewer_service` WHERE `service_id` = ?";
    $DB_STMT = $DB->prepare($DB_SQL);
    # database query not ready
    if (!$DB_STMT) {
        $output = array();
        $output["result"] = -2;
        $output["error"] = "DB PREPARE FAILURE";
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $DB_STMT->bind_param("s", $service_id);
    $DB_STMT->execute();
    if ($DB_STMT->errno != 0) {
        # service info query error
        $output = array();
        $output["result"] = -4;
        $output["error"] = $DB_STMT->error;
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $DB_STMT->bind_result($TEMP_SERVICE_INDEX, $TEMP_SERVICE_EMAIL, $TEMP_SERVICE_PW, $TEMP_SERVICE_CALLED, $TEMP_SERVICE_KEY, $TEMP_SERVICE_CREATED, $TEMP_SERVICE_EXPIRE);
    $DB_STMT->store_result();
    if ($DB_STMT->num_rows != 1) {
        # id is not in database
        $output = array();
        $output["result"] = -3;
        $output["error"] = "ACCOUNT NOT VALID";
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $DB_STMT->fetch();
    $DB_STMT->close();
} catch(Exception $e) {
    # service info query error
    $output = array();
    $output["result"] = -2;
    $output["error"] = $e->getMessage();
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

# password matching
if (!password_verify($service_pw, $TEMP_SERVICE_PW)) {
    $output = array();
    $output["result"] = -3;
    $output["error"] = "ACCOUNT NOT VALID";
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

# execute table deletion query
try {
    $TEMP_TABLE_NAME = "reviewer_".$TEMP_SERVICE_INDEX;
    $DB_SQL = "DROP TABLE $TEMP_TABLE_NAME";
    $DB_STMT = $DB->prepare($DB_SQL);
    # database query not ready
    if (!$DB_STMT) {
        $output = array();
        $output["result"] = -2;
        $output["error"] = $DB->error;
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $DB_STMT->execute();
    if ($DB_STMT->errno != 0) {
        # table deletion query error
        $output = array();
        $output["result"] = -4;
        $output["error"] = $DB_STMT->error;
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $DB_STMT->close();
} catch(Exception $e) {
    # table deletion query error
    $output = array();
    $output["result"] = -2;
    $output["error"] = $e->getMessage();
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

# execute service deletion query
try {
    $DB_SQL = "DELETE FROM `reviewer_service` WHERE `service_id` = ?";
    $DB_STMT = $DB->prepare($DB_SQL);
    # database query not ready
    if (!$DB_STMT) {
        $output = array();
        $output["result"] = -2;
        $output["error"] = $DB->error;
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $DB_STMT->bind_param("s", $service_id);
    $DB_STMT->execute();
    if ($DB_STMT->errno != 0) {
        # service deletion query error
        $output = array();
        $output["result"] = -4;
        $output["error"] = $DB_STMT->error;
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $DB_STMT->close();
} catch(Exception $e) {
    # service deletion query error
    $output = array();
    $output["result"] = -2;
    $output["error"] = $e->getMessage();
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

# service deletion success
$output = array();
$output["result"] = 0;
$output["error"] = "";
$outputJson = json_encode($output);
echo urldecode($outputJson);

?>