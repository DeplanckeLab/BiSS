<?php
	session_start();
	include('variables.php');
	include('bio_utils.php');
	include('php_utils.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<link rel="stylesheet" href="style.css"/>
	<title>Deleting a job</title>
</head>
<body>
	<?php
		// Getting $job
		$params = _test2(get_GET_parameters(array('job')));
		$job = $params['job'];
		// Getting all $jobs, to check if $job inside
		$jobs = _test2(get_jobs(session_id()));
		if (in_array($job, $jobs) === FALSE)
		{
			exit("Trying to delete a non-existent job, are we ? ($job)");
		}
		
		_test(delete_job(session_id(), $job));
	?>
	Everythin' workin' ?
	<script type="text/javascript">
		window.location = 'http://<?php echo $LAB_ADDRESS; ?>/view_jobs.php';
	</script>
	<p>
		Redirecting to the
		<a href="http://<?php echo $LAB_ADDRESS; ?>/view_jobs.php">jobs view</a>...
	</p>
</body>
</html>
