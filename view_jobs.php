<?php
	session_start();
	include('php_utils.php');
	include('bio_utils.php');
	include('variables.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<link rel="stylesheet" href="style.css"/>
	<title>Your jobs</title>
</head>
<body>
	<?php include('top_bar.php'); ?>
	<noscript>
	It seems javascript isn't enabled. This website uses it for
	many fuctionalities (some navigation buttons, sorting and so on),
	so please enable javascript if you want to have full functionality !
	</noscript>
	<?php
		$jobs = _test2(get_jobs(session_id()));
	?>
<!--	<p>
		<button onclick="on_back_to_submit()">Submit another run</button>
	</p>-->
	<table border="1" width="800">
		<tr>
			<th>Job</th>
			<th>Input</th>
			<th>Status</th>
			<th>Log file</th>
			<th>Delete</th>
		</tr>
		<?php
			foreach ($jobs as $job)
			{
				echo '<tr>';
				$status_file = "$UPDIR_PATH/".session_id()."/$FIMO_FOLDER/$job.status";
				if (file_exists($status_file))
				{
					$status = file_get_contents($status_file);
				}
				else
				{
					$status = 'Error';
				}
				echo '<td>';
				if ($status == "Finished\n")
				{
					echo "<a href=\"http://$LAB_ADDRESS/visualisation.php?job=$job\">View result</a>";
				}
				else
				{
					echo $job;
				}
				echo '</td>';
				echo '<td>';
				$upname = _test2(fimo_get_upname(session_id(), $job));
				echo "<a href=\"http://$LAB_ADDRESS/view_input.php?job=$job\">$upname</a>";
				echo '</td>';
				echo '<td>';
				if ($status == "Finished\n")
				{
					echo 'Done !';
				}
				else if ($status == "Running\n")
				{
					echo 'Running...';
				}
				else
				{
					echo $status;
				}
				echo '</td>';
				echo '<td>';
				if ($status == "Running\n")
				{
					echo '-';
				}
				else
				{
					echo "<a href=\"http://$LAB_ADDRESS/view_log.php?job=$job\">$job.log</a>";
				}
				echo '</td>';
				echo '<td>';
				echo "<button onclick=\"delete_job('$job')\">X</button>";
				echo '</td>';
				echo '</tr>';
			}
		?>
	</table>
	<script type="text/javascript">
		function delete_job(job)
		{
			window.location = "<?php echo "http://$LAB_ADDRESS/delete_job.php?job=";?>" + job;
		}
		
/*		function on_back_to_submit()
		{
			window.location = "<?php echo "http://$LAB_ADDRESS/submit_page.php";?>";
		}*/
	</script>
</body>
</html>
