<?php

require_once "./inc.php";

# validate service key
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

# initialize review user
if (isset($_REQUEST["review_user"]))
{
    $review_user = $_REQUEST["review_user"];
} else {
    $review_user = "";
}

# initialize review rating
if (isset($_REQUEST["review_rating"]))
{
    $review_rating = $_REQUEST["review_rating"];
    if (!is_numeric($review_rating)) {
        $output = array();
        $output["result"] = -1;
        $output["error"] = "review_rating IS NOT NUMERIC";
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $review_rating = floatval($review_rating);
} else {
    $review_rating = 0;
}

# initialize review tag
if (isset($_REQUEST["review_tag"]))
{
    $review_tag = $_REQUEST["review_tag"];
} else {
    $review_tag = "";
}

# initialize review title
if (isset($_REQUEST["review_title"]))
{
    $review_title = $_REQUEST["review_title"];
} else {
    $review_title = "";
}

# initialize review_content
if (isset($_REQUEST["review_content"]))
{
    $review_content = $_REQUEST["review_content"];
} else {
    $review_content = "";
}

# execute review insertion query
try {
    /** @noinspection SqlResolve */
    $DB_SQL = "INSERT INTO ? (`review_tag`, `review_user`, `review_rating`, `review_title`, `review_content`, `review_created`) VALUES (?, ?, ?, ?, ?, NOW())";
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
    $TEMP_TABLE_NAME = "reviewer_".$validation["service_index"];
    $DB_STMT->bind_param("sssdss", $TEMP_TABLE_NAME, $review_tag, $review_user, $review_rating, $review_title, $review_content);
    $DB_STMT->execute();
    if ($DB_STMT->errno != 0) {
        # review insertion query error
        $output = array();
        $output["result"] = -4;
        $output["error"] = $DB_STMT->error;
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $TEMP_INSERTED_ROW = $DB_STMT->insert_id;
    $DB_STMT->close();
} catch(Exception $e) {
    # review insertion query error
    $output = array();
    $output["result"] = -2;
    $output["error"] = $e->getMessage();
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

increaseCount($DB, $validation);

# review insertion success
$output = array();
$output["result"] = 0;
$output["error"] = "";
$output["review_index"] = $TEMP_INSERTED_ROW;
$outputJson = json_encode($output);
echo urldecode($outputJson);

?>