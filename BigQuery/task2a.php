<?php
require __DIR__ . '/vendor/autoload.php';

//Reference: [14]"Quickstart: Using client libraries  |  BigQuery  |  Google Cloud", Google Cloud, 2021. [Online]. Available: https://cloud.google.com/bigquery/docs/quickstarts/quickstart-client-libraries. [Accessed: 10- Apr- 2021].
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Core\ExponentialBackoff;

/** Uncomment and populate these variables in your code */
$projectId = 's3804158-as1-task2';
$query = 'SELECT time_ref, SUM(value) as Tradevalue
FROM task2.gsquarterlySeptember20
GROUP BY time_ref 
ORDER BY SUM(value) DESC 
LIMIT 10';

$bigQuery = new BigQueryClient([
	'projectId' => $projectId,
]);
$jobConfig = $bigQuery->query($query);
$job = $bigQuery->startQuery($jobConfig);

$backoff = new ExponentialBackoff(10);
$backoff->execute(function () use ($job) {
	print('Waiting for job to complete' . PHP_EOL);
	$job->reload();
	if (!$job->isComplete()) {
		throw new Exception('Job has not yet completed', 500);
	}
});
$queryResults = $job->queryResults();

?>
<!DOCTYPE>
<html>

<head>
	<meta charset="utf-8" />
	<meta name="description" content="Cloud Computing, Assignment 1" />
	<meta name="keywords" content="PHP, Google Cloud" />
	<meta name="author" content="Ruvinda Ranaweera - s3804158" />
	<title>Task2</title>

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>

<body>
	<div class="container p-3 my-3 border">
		<h1>Task2A</h1>
	</div>
	<a href="/">Back</a>
	<div class="container">
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12">
				<div class="well well-sm">
					<div class="row">
						<div class="col-sm-12 col-md-12">
							<table class="table table-striped">
								<thead>
									<tr>
										<th>Time_ref</th>
										<th>Trade value</th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ($queryResults as $row) {
										echo '<tr class="active">';
										foreach ($row as $column => $value) {	
											echo '<td>' . $value. '</td>';	
										}
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