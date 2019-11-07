<?php

require_once "./inc.php";

# initialize service key
if (isset($_REQUEST["service_key"]))
{
    $service_key = $_REQUEST["service_key"];
    $validation = validateKey($DB, $service_key);
} else {
    $output = array();
    $output["result"] = -1;
    $output["error"] = "service_key IS EMPTY";
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

# initialize review offset
if (isset($_REQUEST["review_offset"]))
{
    $review_offset = $_REQUEST["review_offset"];
    if (!is_numeric($review_offset)) {
        $output = array();
        $output["result"] = -1;
        $output["error"] = "review_offset IS NOT NUMERIC";
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
} else {
    $review_offset = 0;
}

# initialize review limit
if (isset($_REQUEST["review_limit"]))
{
    $review_limit = $_REQUEST["review_limit"];
    if (!is_numeric($review_limit)) {
        $output = array();
        $output["result"] = -1;
        $output["error"] = "review_limit IS NOT NUMERIC";
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
} else {
    $review_limit = 100;
}

# initialize review order
if (isset($_REQUEST["review_order"]))
{
    $review_order = $_REQUEST["review_order"];
    if (!is_numeric($review_order)) {
        $output = array();
        $output["result"] = -1;
        $output["error"] = "review_order IS NOT NUMERIC";
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
} else {
    $review_order = 0;
}


$reviewResult = array();
# execute service reviews query
try {
    $TEMP_TABLE_NAME = "reviewer_".$validation["service_index"];
    /** @noinspection SqlResolve */
    $DB_SQL = "SELECT `review_index`, `review_tag`, `review_user`, `review_rating`, `review_title`, `review_content`, `review_created` FROM $TEMP_TABLE_NAME";
    if ($review_order == 0) $DB_SQL .= " ORDER BY `review_index` ASC";
    if ($review_order == 1) $DB_SQL .= " ORDER BY `review_index` DESC";
    if ($review_order == 2) $DB_SQL .= " ORDER BY `review_rating` ASC";
    if ($review_order == 3) $DB_SQL .= " ORDER BY `review_rating` DESC";
    $DB_SQL .= " LIMIT ".$review_offset.", ".$review_limit;
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
    $DB_STMT->execute();
    if ($DB_STMT->errno != 0) {
        # service reviews query error
        $output = array();
        $output["result"] = -4;
        $output["error"] = $DB_STMT->error;
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $DB_STMT->bind_result($TEMP_REVIEW_INDEX, $TEMP_REVIEW_TAG, $TEMP_REVIEW_USER, $TEMP_REVIEW_RATING, $TEMP_REVIEW_TITLE, $TEMP_REVIEW_CONTENT, $TEMP_REVIEW_CREATED);
    while($DB_STMT->fetch()) {
        $reviewObject = array();
        $reviewObject["review_index"] = $TEMP_REVIEW_INDEX;
        $reviewObject["review_tag"] = $TEMP_REVIEW_TAG;
        $reviewObject["review_user"] = $TEMP_REVIEW_USER;
        $reviewObject["review_rating"] = $TEMP_REVIEW_RATING;
        $reviewObject["review_title"] = $TEMP_REVIEW_TITLE;
        $reviewObject["review_content"] = $TEMP_REVIEW_CONTENT;
        $reviewObject["review_created"] = $TEMP_REVIEW_CREATED;
        array_push($reviewResult, $reviewObject);
    }
    $DB_STMT->close();
} catch(Exception $e) {
    # service reviews query error
    $output = array();
    $output["result"] = -2;
    $output["error"] = $e->getMessage();
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

# review list success
$output = array();
$output["result"] = 0;
$output["error"] = "";
$output["reviews"] = $reviewResult;
$outputJson = json_encode($output);
echo urldecode($outputJson);

?>