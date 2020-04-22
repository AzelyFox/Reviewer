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
    $service_pw = password_hash($_REQUEST["service_pw"], PASSWORD_BCRYPT);
} else {
    $output = array();
    $output["result"] = -1;
    $output["error"] = "service_pw IS EMPTY";
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

# initialize service email
if (isset($_REQUEST["service_email"]))
{
    $service_email = $_REQUEST["service_email"];
    if (mb_strlen($service_email) < 4 || mb_strlen($service_email) > 40) {
        $output = array();
        $output["result"] = -1;
        $output["error"] = "service_email IS NOT VALID";
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
} else {
    $output = array();
    $output["result"] = -1;
    $output["error"] = "service_email IS EMPTY";
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}


# generate session key
$GENERATOR_CHARACTERS = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$GENERATOR_CHARACTERS_LENGTH = strlen($GENERATOR_CHARACTERS);
$GENERATOR_LENGTH = 12;
$GENERATOR_RESULT = "";
for ($i = 0; $i < $GENERATOR_LENGTH; $i++) {
    $GENERATOR_RESULT .= $GENERATOR_CHARACTERS[rand(0, $GENERATOR_CHARACTERS_LENGTH - 1)];
}

# execute service creation query
try {
    $DB_SQL = "INSERT INTO `reviewer_service` (`service_id`, `service_email`, `service_pw`, `service_key`, `service_created`, `service_expire`) VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 90 DAY))";
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
    $DB_STMT->bind_param("ssss", $service_id, $service_email, $service_pw, $GENERATOR_RESULT);
    $DB_STMT->execute();
    if ($DB_STMT->errno != 0) {
        # service creation query error
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
    # service creation query error
    $output = array();
    $output["result"] = -2;
    $output["error"] = $e->getMessage();
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

# execute table creation query
try {
    $TEMP_TABLE_NAME = "reviewer_".$TEMP_INSERTED_ROW;
    $DB_SQL = "CREATE TABLE $TEMP_TABLE_NAME (`review_index` INT NOT NULL AUTO_INCREMENT COMMENT 'review index', `review_tag` VARCHAR(60) NOT NULL COMMENT 'review tag', `review_user` VARCHAR(60) NOT NULL COMMENT 'review user', `review_rating` FLOAT NOT NULL COMMENT 'review rating', `review_title` VARCHAR(100) NOT NULL COMMENT 'review title', `review_content` VARCHAR(200) NOT NULL COMMENT 'review content', `review_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'review created time', PRIMARY KEY (`review_index`));";
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
        # table creation query error
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
    # table creation query error
    $output = array();
    $output["result"] = -2;
    $output["error"] = $e->getMessage();
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

# service creation success
$output = array();
$output["result"] = 0;
$output["error"] = "";
$output["service_index"] = $TEMP_INSERTED_ROW;
$output["service_id"] = $service_id;
$output["service_email"] = $service_email;
$output["service_key"] = $GENERATOR_RESULT;
$outputJson = json_encode($output);
echo urldecode($outputJson);

?>