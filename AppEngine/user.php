<?php
// save buffer
ob_start();
// session start function to use session variables
session_start();
// Reference: [8]"Datastore mode Client Libraries  |  Cloud Datastore Documentation", Google Cloud, 2021. [Online]. Available: https://cloud.google.com/datastore/docs/reference/libraries. [Accessed: 09- Apr- 2021].
// require autoload.php in vendor folder to use Composer for php
require __DIR__ . '/vendor/autoload.php';

$imageURL = 'https://storage.googleapis.com/cloud_as1/Images/' . $_SESSION['id'] . '.PNG';

// Reference: [7]"Entities, Properties, and Keys  |  Cloud Datastore Documentation", Google Cloud, 2021. [Online]. Available: https://cloud.google.com/datastore/docs/concepts/entities. [Accessed: 09- Apr- 2021].
use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Storage\StorageClient;

// Connect to GCloud Datastore
$datastore = new DatastoreClient(['projectId' => 'testapprkr12']);

// Set server Timezone to Melbourne
date_default_timezone_set('Australia/Melbourne');

// Set notification variable
$msg = '';

// check if both fields are assigned of value
if (isset($_POST['oldpassword']) && isset($_POST['newpassword'])) {

    if ($_POST['oldpassword'] == null || $_POST['newpassword'] == null) {
        // print notice and stop php execution
        $msg = "NOTICE: Please complete the form to to change password!";
    } else {

        // assign both values to php variables
        $oldPW = $_POST['oldpassword'];
        $newPW = $_POST['newpassword'];

        // check if both input variables are numeric
        if (!is_numeric($oldPW) || !is_numeric($newPW)) {
            $msg = "NOTICE: Password only accept numeric inputs!";
        } else {
            // get user ID from session variable from login.php
            $userID = $_SESSION['id'];

            // get key corresponding to user ID
            $userKey = $datastore->key('user', $userID);

            // lookup for entity corresponding to user ID
            $userTask = $datastore->lookup($userKey);

            // compare password in Datastore and convert string to integer
            if ($userTask['password'] != intval($oldPW)) {
                $msg = "NOTICE: Old Password does not match!";
            } else {
                // if all validations passed, proceed with data update
                // begin transaction
                $transaction = $datastore->transaction();
                $userTask = $transaction->lookup($userKey);

                // set password in Datastore to new Password
                $userTask['password'] = intval($newPW);

                // update function to update Datastore
                $transaction->update($userTask);

                // commit function to save changes
                $transaction->commit();

                $msg = "Password updated successfully!";
            }
        }
    }
}

// Reference : [3]"Datastore Queries  |  App Engine standard environment for Go 1.11 docs", Google Cloud, 2021. [Online]. Available: https://cloud.google.com/appengine/docs/standard/go111/datastore/queries. [Accessed: 09- Apr- 2021].
// Query current user posts
$query = $datastore->query()
    ->kind('post')
    ->filter('userid', '=', $_SESSION['id']);

//Reference : [4]"Multidimensional array in PHP", Plus2net.com, 2021. [Online]. Available: https://www.plus2net.com/php_tutorial/array-multidimensional.php. [Accessed: 09- Apr- 2021].
$data = array(0 => array("key" => "", "image" => "", "subject" => "", "message" => "", "datetime" => ""));
$result = $datastore->runQuery($query);

/* @var Entity $task */
foreach ($result as $task) {
    $newdata = array('key' => $task->key()->pathEndIdentifier(), 'image' => $task['image'], 'subject' => $task['subject'], 'message' => $task['message'], 'datetime' => $task['datetime']);
    array_push($data, $newdata);
}

// clean buffer
ob_end_flush();
?>

<!DOCTYPE>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="description" content="Cloud Computing, Assignment 1" />
    <meta name="keywords" content="PHP, Google Cloud" />
    <meta name="author" content="Ruvinda Ranaweera - s3804158" />
    <title>Main Page</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link type="text/css" href="stylesheets/main.css" rel="stylesheet">
</head>

<body>
    <!-- Main page after login, with display of username using session variable from login.php or name.php -->

    <div class="container p-3 my-3 border">
        <h1>Main Page</h1>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="well well-sm">
                    <div class="row">
                        <div class="col-sm-12 col-md-12">
                            <div class="signup-form">
                                <form action="/user.php" method="post" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <a href="main.php">Back</a>
                                    </div>
                                    <h2>Change Password</h2>
                                    <p class="hint-text">Enter old and new password to change</p>
                                    <div class="form-group">
                                        <input type="password" name="oldpassword" id="oldpassword" class="form-control" placeholder="Old Password" required="required">
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="newpassword" id="newpassword" class="form-control" placeholder="New Password" required="required">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-block" value="ChangePassword">Update</button>
                                    </div>
                                    <div class="form-group">
                                        <?php echo $msg ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="well well-sm">
                    <div class="row">
                        <div class="col-sm-12 col-md-12">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Post Image</th>
                                        <th>Subject</th>
                                        <th>Message</th>
                                        <th>Posted Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($data as $task) {
                                        echo '<tr class="active">';
                                        echo '<td><img src="' . $task['image'] . '" class="img-rounded img-responsive" width="75" height="75" /></td>';
                                        echo '<td>' . $task['subject'] . '</td>';
                                        echo '<td>' . $task['message'] . '</td>';
                                        echo '<td>' . $task['datetime'] . '</td>';
                                        echo '<td><a href="edit.php?postid=' . $task['key'] . '">Edit</a></td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>