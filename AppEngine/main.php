<?php
// save buffer
ob_start();
// session start function to use session variables
session_start();
// Reference: [8]"Datastore mode Client Libraries  |  Cloud Datastore Documentation", Google Cloud, 2021. [Online]. Available: https://cloud.google.com/datastore/docs/reference/libraries. [Accessed: 09- Apr- 2021].
require __DIR__ . '/vendor/autoload.php';

$imageURL = 'https://storage.googleapis.com/cloud_as1/Images/' . $_SESSION['id'] . '.PNG';

// Reference: [7]"Entities, Properties, and Keys  |  Cloud Datastore Documentation", Google Cloud, 2021. [Online]. Available: https://cloud.google.com/datastore/docs/concepts/entities. [Accessed: 09- Apr- 2021].
use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Storage\StorageClient;

// Connect to GCloud Datastore
$datastore = new DatastoreClient(['projectId' => 'testapprkr12']);

// Set server Timezone to Melbourne
date_default_timezone_set('Australia/Melbourne');

if ($_GET) {
    $msg = $_GET['msg'];
}

// check if both fields are assigned of value
if (isset($_POST['subject']) && isset($_POST['message'])) {

    if ($_POST['subject'] == null || $_POST['message'] == null) {
        $msg = "NOTICE: Please complete the form to post!";
    } else {
        // assign input values to php variables
        $userID = $_SESSION['id'];
        $userName = $_SESSION['username'];
        $bucketName = 'cloud_as1';
        $objectName = $_FILES["image"]["name"];
        $source = $_FILES["image"]["tmp_name"];


        // Reference: [9]C. [duplicate] and R. S, "Check file extension in upload form in PHP", Stack Overflow, 2021. [Online]. Available: https://stackoverflow.com/questions/10456113/check-file-extension-in-upload-form-in-php. [Accessed: 09- Apr- 2021].
        // check upload file type
        $allowed = array('png', 'jpg', 'PNG', 'JPG', 'JPEG');
        $ext = pathinfo($objectName, PATHINFO_EXTENSION);
        if (!in_array($ext, $allowed)) {
            $msg = "NOTICE: Only image are files allowed!";
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

                # Prepares the new entity
                $task = $datastore->entity('post', [
                    'image' => $imageURL,
                    'userid' => $userID,
                    'subject' => $_POST['subject'],
                    'message' => $_POST['message'],
                    'datetime' => date('m/d/Y h:i:s a', time())
                ]);
                $datastore->insert($task);

                $msg = 'NOTICE: Message Post Successful!';
                header("location: /main.php?msg=" . $msg);
            } catch (Exception $e) {
                $msg = 'NOTICE: Message Post Unsuccessful!';
            }
        }
    }
}

// Reference : [3]"Datastore Queries  |  App Engine standard environment for Go 1.11 docs", Google Cloud, 2021. [Online]. Available: https://cloud.google.com/appengine/docs/standard/go111/datastore/queries. [Accessed: 09- Apr- 2021].
$query = $datastore->query()
    ->kind('post');

//Reference : [4]"Multidimensional array in PHP", Plus2net.com, 2021. [Online]. Available: https://www.plus2net.com/php_tutorial/array-multidimensional.php. [Accessed: 09- Apr- 2021].
$data = array(0 => array("image" => "", "subject" => "", "message" => "", "datetime" => "", "username" => "", "userimage" => ""));
$result = $datastore->runQuery($query);

foreach ($result as $task) {

    // get key from User Kind and corresponding user ID from GCloud Datastore
    $userKey = $datastore->key('user', $task['userid']);

    // use key to lookup for entity
    $userTask = $datastore->lookup($userKey);

    $newdata = array('image' => $task['image'], 'subject' => $task['subject'], 'message' => $task['message'], 'datetime' => $task['datetime'], "username" => $userTask['user_name'], "userimage" => $userTask['image']);
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
    <!-- Reference: [5]"HTML Snippets for Twitter Boostrap framework : Bootsnipp.com", Bootsnipp.com, 2021. [Online]. Available: https://bootsnipp.com/snippets/84M5. [Accessed: 09- Apr- 2021]. -->
    <link type="text/css" href="stylesheets/main.css" rel="stylesheet">
</head>

<body>
    <div class="container p-3 my-3 border">
        <h1>Main Page</h1>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="well well-sm">
                    <div class="row">
                        <div class="col-sm-6 col-md-4">
                            <?php echo '<img src="' . $imageURL . '" class="img-rounded img-responsive" width="120" height="120" />'; ?>
                        </div>
                        <div class="col-sm-6 col-md-8">
                            <h4>Welcome <a href="user.php"><?php echo $_SESSION['username']; ?></a></h4>
                            <br />
                            <h4>User ID : <i class="bi bi-person-badge"></i><?php echo $_SESSION['id']; ?></h4>
                            <br />
                            <a href="/logout.php" class="btn btn-primary">Logout</a>
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
                            <div class="signup-form">
                                <form action="/main.php" method="post" enctype="multipart/form-data">
                                    <h2>New Post</h2>
                                    <p class="hint-text">Post Your New Messages Here</p>
                                    <div class="form-group">
                                        <input type="text" name="subject" id="subject" class="form-control" placeholder="Subject" required="required">
                                    </div>
                                    <div class="form-group">
                                        <textarea type="text" name="message" id="message" class="form-control" placeholder="Enter your post here" required="required"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <input type="file" name="image" id="image" class="form-control" required="required">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-block" value="Register">Post</button>
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
                                        <th>Date</th>
                                        <th>Posted By</th>
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
                                        echo '<td><img src="' . $task['userimage'] . '" class="img-rounded img-responsive" width="50" height="50" /><br />' . $task['username'] . '</td>';
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