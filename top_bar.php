<?php
	include('variables.php');
?>
<div id="top_bar">
	<div id="top_bar_biss">BiSS</div>
	<div id="top_bar_to_runs" onclick="top_bar_to_runs()">Submit a run</div>
	<div id="top_bar_to_jobs" onclick="top_bar_to_jobs()">View runs</div>
	<div id="top_bar_to_faq" onclick="top_bar_to_faq()">FAQ</div>
</div>

<script language="JavaScript">
	function top_bar_to_runs()
	{
		window.location = 'http://<?php echo $LAB_ADDRESS; ?>/submit_page.php';
	}
	function top_bar_to_jobs()
	{
		window.location = 'http://<?php echo $LAB_ADDRESS; ?>/view_jobs.php';
	}
	function top_bar_to_faq()
	{
		window.location = 'http://<?php echo $LAB_ADDRESS; ?>/faq.php';
	}
</script>
