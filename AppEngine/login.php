<?php
// save buffer
ob_start();
// session start function to use session variables
session_start();
// Reference: [8]"Datastore mode Client Libraries  |  Cloud Datastore Documentation", Google Cloud, 2021. [Online]. Available: https://cloud.google.com/datastore/docs/reference/libraries. [Accessed: 09- Apr- 2021].
// require autoload.php in vendor folder to use Composer for php
require __DIR__ . '/vendor/autoload.php';

// Reference: [7]"Entities, Properties, and Keys  |  Cloud Datastore Documentation", Google Cloud, 2021. [Online]. Available: https://cloud.google.com/datastore/docs/concepts/entities. [Accessed: 09- Apr- 2021].
use Google\Cloud\Datastore\DatastoreClient;

if ($_GET) {
    $msg = $_GET['msg'];
}
// check if both fields are assigned of value
if (isset($_POST['userid']) && isset($_POST['password'])) {
    if ($_POST['userid'] == null || $_POST['password'] == null) {
        echo "NOTICE: Please complete the form to login!";
    } else {
        // assign input values to php variables
        $userID = $_POST['userid'];
        $pword = $_POST['password'];

        // check if password is numeric
        if (!is_numeric($pword)) {
            $msg = "NOTICE: Password only accept numeric inputs!";
        } else {
            // Connect to GCloud Datastore
            $datastore = new DatastoreClient(['projectId' => 'testapprkr12']);

            // get key from User Kind and corresponding user ID from GCloud Datastore
            $userKey = $datastore->key('user', $userID);

            // use key to lookup for entity
            $userTask = $datastore->lookup($userKey);

            // check if input password matches password in Datastore
            if ($userTask['password'] == intval($pword)) {
                // save user ID and username under session variables
                $_SESSION['id'] = $userID;
                $_SESSION['username'] = $userTask['user_name'];

                // redirect login.php to main.php
                header("location: main.php");
            } else {
                // print notice and stop php execution
                $msg =  "NOTICE: Login Unsuccessful! ID or password is invalid";
            }
        }
    }
}

// clean buffer
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="description" content="Cloud Computing, Assignment 1" />
    <meta name="keywords" content="PHP, Google Cloud" />
    <meta name="author" content="Ruvinda Ranaweera - s3804158" />
    <title>Login Page</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script> 
    <!-- Reference: [11]"HTML Snippets for Twitter Boostrap framework : Bootsnipp.com", Bootsnipp.com, 2021. [Online]. Available: https://bootsnipp.com/snippets/bxzmb. [Accessed: 09- Apr- 2021].-->
    <link type="text/css" href="stylesheets/signin.css" rel="stylesheet">
</head>
<body>
<div class="login-form">
    <form action="/" method="post">
        <div class="form-group">
            <?php echo $msg ?>
        </div>  
        <h2 class="text-center">Log in</h2>    
        <div class="form-group">
            <input type="text" name="userid" id="userid" class="form-control" placeholder="UserID" required="required">
        </div>
        <div class="form-group">
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required="required">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">Log in</button>
        </div>  
        <div class="form-group">
            <p class="text-center"><a href="register.php">Create an Account</a></p> 
        </div>       
    </form>
</div>
</body>
</html>