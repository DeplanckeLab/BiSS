<?php
	session_start();
	include('bio_utils.php');
	include('php_utils.php');
	include('variables.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<link rel="stylesheet" href="style.css"/>
	<title>Submitting the job...</title>
</head>
<body>
	<?php
		// First we get the sequence from the POST data (either in the
		// text box (id='sequence') or in the file input (id='fasta')
		list($raw, $upname) = get_sequence($_POST['sequence'], $_FILES['fasta']);
		_test(pre_check_input($raw));
		// Getting input as FASTA
		$seqs = format_sequence_into_sequences($raw);
		_test(check_sequences($seqs));
		// Creating user context (folder, etc) if not existing already
		_test(create_context_for_user(session_id()));
		// Preparing context for a fimo run ; job number is decided now
		$job = _test2(move_input(session_id(), $seqs, $upname));
		launch_fimo(session_id(), $job, '');
	?>
	<script type="text/javascript">
		window.location = 'http://<?php echo $LAB_ADDRESS; ?>/view_jobs.php';
	</script>
	<p>
		Redirecting to the
		<a href="http://<?php echo $LAB_ADDRESS; ?>/view_jobs.php">jobs view</a>...
	</p>
</body>
</html>
