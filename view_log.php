<?php
	session_start();
	include('bio_utils.php');
	include('php_utils.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<link rel="stylesheet" href="style.css"/>
	<title>Viewing a job</title>
</head>
<body>
	<?php include('top_bar.php'); ?>
	<?php
		// Getting $job and $status
		$params = _test2(get_GET_parameters(array('job')));
		$job = $params['job'];
		// Getting actual content
		$raw = _test2(fimo_get_log(session_id(), $job));
		$nice = implode('<br/>', explode("\n", $raw));
		echo $nice;
	?>
</body>
</html>
