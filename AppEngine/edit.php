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

// Connect to GCloud Datastore
$datastore = new DatastoreClient(['projectId' => 'testapprkr12']);

// Set server Timezone to Melbourne
date_default_timezone_set('Australia/Melbourne');

$userID = $_SESSION['id'];

// check if postid is sent from user page
if (isset($_GET['postid'])) {
    // get post ID from passed from GET
    $postID = $_GET['postid'];

    // get key corresponding to user ID
    $postKey = $datastore->key('post', $postID);

    // lookup for entity corresponding to user ID
    $postTask = $datastore->lookup($postKey);

    // assign data to the variables to preload
    $subject = $postTask['subject'];
    $message = $postTask['message'];
    $imageURL = $postTask['image'];
}

// check if both fields are assigned of value
if (isset($_POST['subject']) && isset($_POST['message'])) {

    if ($_POST['subject'] == null || $_POST['message'] == null) {
        // print notice and stop php execution
        $msg = "NOTICE: Post Update Unsuccessful!";
    } else {
        // assign input values to php variables
        $subject = $_POST['subject'];
        $message = $_POST['message'];
        $postID = $_POST['postid'];

        // check if new image is uploaded
        if ($_FILES["image"]["name"]) {
            $objectName = $_FILES["image"]["name"];
            $source = $_FILES["image"]["tmp_name"];
            $bucketName = 'cloud_as1';

            // Reference: [9]C. [duplicate] and R. S, "Check file extension in upload form in PHP", Stack Overflow, 2021. [Online]. Available: https://stackoverflow.com/questions/10456113/check-file-extension-in-upload-form-in-php. [Accessed: 09- Apr- 2021].
            // check upload file type
            $allowed = array('png', 'jpg', 'PNG', 'JPG', 'JPEG');
            $ext = pathinfo($objectName, PATHINFO_EXTENSION);
            if (!in_array($ext, $allowed)) {
                $msg = "NOTICE: Post Update Unsuccessful!";
            } else {

                try {
                    $imageLocation = 'Images/Posts/' . $userID . '/' . $_FILES["image"]["name"] . '.PNG';
                    $imageURL = 'https://storage.googleapis.com/cloud_as1/' . $imageLocation;

                    // Reference: [6]"Cloud Storage Client Libraries  |  Google Cloud", Google Cloud, 2021. [Online]. Available: https://cloud.google.com/storage/docs/reference/libraries#client-libraries-install-php. [Accessed: 09- Apr- 2021].
                    # Prepare new cloud storage image object
                    $storage = new StorageClient();
                    $file = fopen($source, 'r');
                    $bucket = $storage->bucket($bucketName);
                    $object = $bucket->upload($file, [
                        'name' => $imageLocation
                    ]);
                } catch (Exception $e) {
                    $msg = "NOTICE: Post Update Unsuccessful!";
                }
            }
        }else{
            $imageURL = $_POST['oldimage'];
        }

        try {
            // begin transaction
            // get key corresponding to post ID
            $postKey = $datastore->key('post', $postID);
            $transaction = $datastore->transaction();
            $postTask = $transaction->lookup($postKey);

            // set password in Datastore to new Password
            $postTask['subject'] = $subject;
            $postTask['message'] = $message;
            $postTask['image'] = $imageURL;
            $postTask['datetime'] = date('m/d/Y h:i:s a', time());

            // update function to update Datastore
            $transaction->update($postTask);

            // commit function to save changes
            $transaction->commit();

            $msg = "Post updated successfully!";
            header("location: /main.php?msg=" . $msg);
        } catch (\Throwable $th) {
            $msg = "NOTICE: Post Update Unsuccessful!";
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
    <link type="text/css" href="stylesheets/main.css" rel="stylesheet">
</head>

<body>

    <div class="container p-3 my-3 border">
        <h1>Post Edit Page</h1>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="well well-sm">
                    <div class="row">
                        <div class="col-sm-12 col-md-12">
                            <div class="signup-form">
                                <form action="/edit.php" method="post" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <a href="user.php">Back</a>
                                    </div>
                                    <h2>Change Password</h2>
                                    <p class="hint-text">Update you post here</p>
                                    <!-- Pass postid & imageURL through hidden input field -->
                                    <input type="hidden" name="postid" value="<?php echo $postID;?>" />
                                    <input type="hidden" name="oldimage" value="<?php echo $imageURL;?>" />
                                    <div class="form-group">
                                        <label for="subject">Subject</label>
                                        <input type="text" name="subject" id="subject" class="form-control" value="<?php echo $subject; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="message">Message</label>
                                        <textarea type="text" name="message" id="message" class="form-control" ><?php echo $message; ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-6 col-md-4">
                                            <label for="image">Image</label>
                                            <?php echo '<img src="' . $imageURL . '" class="img-rounded img-responsive" width="120" height="120" />'; ?>
                                        </div>
                                        <input type="file" name="image" id="image" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-block" value="Update">Update</button>
                                    </div>
                                    <div class="form-group">
                                        <?php echo $msg ?>
                                    </div>
                                    <div class="form-group">
                                        <?php echo $msg2 ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>