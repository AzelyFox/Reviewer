<?php

require_once "./inc.php";

# key auth
if (isset($_REQUEST["key"]))
{
    $key = $_REQUEST["key"];
    if (!is_string($key)) {
        $output = array();
        $output["result"] = -1;
        $output["error"] = "key MUST BE STRING";
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    if ($key != $MANAGER_KEY) {
        $output = array();
        $output["result"] = -3;
        $output["error"] = "key NOT VALID";
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
} else {
    $output = array();
    $output["result"] = -1;
    $output["error"] = "key IS EMPTY";
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

# prepare service due list result
$serviceDueResult = array();

# execute service due list query
try {
    $DB_SQL = "SELECT `service_index` FROM `reviewer_service` WHERE `service_expire` < NOW()";
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
        # service due list query error
        $output = array();
        $output["result"] = -4;
        $output["error"] = $DB_STMT->error;
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $DB_STMT->bind_result($TEMP_SERVICE_INDEX);
    while($DB_STMT->fetch()) {
        array_push($serviceDueResult, $TEMP_SERVICE_INDEX);
    }
    $DB_STMT->close();
} catch(Exception $e) {
    # service due list query error
    $output = array();
    $output["result"] = -2;
    $output["error"] = $e->getMessage();
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

foreach ($serviceDueResult as $dueIndex) {
    # execute table deletion query
    try {
        $TEMP_TABLE_NAME = "reviewer_".$dueIndex;
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
        $DB_SQL = "DELETE FROM `reviewer_service` WHERE `service_index` = ?";
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
        $DB_STMT->bind_param("i", $dueIndex);
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
}

# service manage success
$output = array();
$output["result"] = 0;
$output["error"] = "";
$outputJson = json_encode($output);
echo urldecode($outputJson);

?>