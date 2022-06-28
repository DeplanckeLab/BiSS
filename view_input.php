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
		$raw = _test2(fimo_get_input(session_id(), $job));
		$seqs = format_sequence_into_sequences($raw);
		$keys = array_keys($seqs);
		foreach ($keys as $key)
		{
			echo "<p id=\"fasta\">\n";
			echo $key . "<br/>\n";
			$strs = str_split($seqs[$key], 100);
			foreach ($strs as $str)
			{
				echo $str . "<br/>\n";
			}
			echo "</p>\n";
		}
	?>
</body>
</html>
