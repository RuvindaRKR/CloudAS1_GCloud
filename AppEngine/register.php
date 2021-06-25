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
use Google\Cloud\Storage\StorageClient;

// check if both fields are assigned of value
if (isset($_POST['userid']) && isset($_POST['password'])) {

    if ($_POST['userid'] == null || $_POST['password'] == null || $_FILES["image"]["name"] == null) {
        echo "NOTICE: Please complete the form to register!";
    } else {
        // assign input values to php variables
        $userID = $_POST['userid'];
        $userName = $_POST['username'];
        $password = $_POST['password'];
        $bucketName = 'cloud_as1';
        $objectName = $_FILES["image"]["name"];
        $source = $_FILES["image"]["tmp_name"];


        // Reference: [9]C. [duplicate] and R. S, "Check file extension in upload form in PHP", Stack Overflow, 2021. [Online]. Available: https://stackoverflow.com/questions/10456113/check-file-extension-in-upload-form-in-php. [Accessed: 09- Apr- 2021].
        // check upload file type
        $allowed = array('png', 'jpg', 'PNG', 'JPG', 'JPEG');
        $ext = pathinfo($objectName, PATHINFO_EXTENSION);
        if (!in_array($ext, $allowed)) {
            echo "NOTICE: Only image are files allowed!";
        } elseif (!is_numeric($password)) { // check if password is numeric
            // print notice and stop php execution
            echo "NOTICE: Password only accept numeric inputs!";
        } else {
            // Connect to GCloud Datastore
            $datastore = new DatastoreClient(['projectId' => 'testapprkr12']);

            // The Cloud Datastore key for the new entity
            $userKey = $datastore->key('user', $userID);

            // use key to lookup for entity
            $userTask = $datastore->lookup($userKey);

            // Reference : [3]"Datastore Queries  |  App Engine standard environment for Go 1.11 docs", Google Cloud, 2021. [Online]. Available: https://cloud.google.com/appengine/docs/standard/go111/datastore/queries. [Accessed: 09- Apr- 2021].
            // check if username exists
            $query = $datastore->query()
            ->kind('user')
            ->filter('user_name', '=', $userName);
            $result = $datastore->runQuery($query);

            $uNameExists = false; 
            foreach ($result as $usernames) {
                if(!empty($usernames['user_name']))
                        $uNameExists = true; 
            }

            // check if input password matches password in Datastore
            if ($uNameExists == true) {
                echo "NOTICE: Username already exists!!";
            } elseif (!empty($userTask)) {
                echo "NOTICE: UserID already exists!!";
            } else {

                try {
                    $imageLocation = 'Images/' . $userID . '.PNG';
                    $imageURL = 'https://storage.googleapis.com/cloud_as1/' . $imageLocation;

                    # Prepare new cloud storage image object
                    $storage = new StorageClient();
                    $file = fopen($source, 'r');
                    $bucket = $storage->bucket($bucketName);
                    $object = $bucket->upload($file, [
                        'name' => $imageLocation
                    ]);

                    # Prepares the new entity
                    $task = $datastore->entity($userKey, [
                        'user_name' => $userName,
                        'password' => $password,
                        'image' => $imageURL
                    ]);

                    # Saves the entity
                    $datastore->upsert($task);
                    echo "Registraion Successful!";
                    $msg = "Registraion Successful!";
                    header("location: /?msg=" . $msg);
                } catch (Exception $e) {
                    echo 'NOTICE: Registration Unsuccessful!';
                }
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
    <title>Register Page</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script> 
    <!-- Reference: [12]"Bootstrap Simple Registration Form Template", Tutorialrepublic.com, 2021. [Online]. Available: https://www.tutorialrepublic.com/snippets/preview.php?topic=bootstrap&file=simple-registration-form. [Accessed: 09- Apr- 2021]. -->
    <link type="text/css" href="stylesheets/register.css" rel="stylesheet">
</head>
<body>
    
<div class="signup-form">
    <form action="/register.php" method="post" enctype="multipart/form-data">
		<h2>Register</h2>
		<p class="hint-text">Create your account.</p>
        <div class="form-group">
        	<input type="text" name="userid" id="userid" class="form-control"  placeholder="UserID" required="required">
        </div>
        <div class="form-group">
        	<input type="text" name="username" id="username" class="form-control"  placeholder="Username" required="required">
        </div>
		<div class="form-group">
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required="required">
        </div>
		<div class="form-group">
            <input type="file" name="image" id="image"class="form-control" required="required">
        </div>        
		<div class="form-group">
            <button type="submit" class="btn btn-primary btn-block" value="Register">Register Now</button>
        </div>
    </form>
	<div class="text-center">Already have an account? <a href="/">Sign in</a></div>
</div>

</body>
</html>