<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<link rel="stylesheet" href="style.css"/>
	<title>Y1H screening</title>
</head>
<body>
	<?php include('top_bar.php'); ?>
	<noscript>
	It seems javascript isn't enabled. This website uses it for
	many functionalities (some navigation buttons, sorting and so on),
	so please enable javascript if you want to have full functionality !
	</noscript>
<!--	<p>
		We are using <a href="http://meme.nbcr.net/meme/fimo-intro.html" target="_blank">FIMO</a> to screen
	</p>-->
	<p>
		<a href="http://<?php echo $LAB_ADDRESS; ?>/new_here.php">New here ? Please read this.</a>
	</p>
	<form method="post" action="submit_job.php" enctype="multipart/form-data">
		<p>
			Enter your FASTA file or the sequence :
			<p>
				Enter either a sequence<br/>
				<textarea name="sequence" rows="8" cols="45"></textarea>
			</p>
			<p>
				or a file (in FASTA format)<br/>
				<input type="file" name="fasta">
			</p>
		</p>
		<input type="submit" value="Send">
	</form>
<!--	<p>
		Or maybe you want to <a href="javascript:void" id="goto_jobs_view">skip to view the jobs launched</a>.
	</p>-->
<!--	<script type="text/javascript">
		function on_goto_jobs()
		{
			var url = document.URL;
			var split = url.split("/");
			var base_address = split.slice(2, split.length-1).join("/");
			
			window.location = "http://" + base_address + "/view_jobs.php";
		}
		
		(function(){
			var on_goto = document.getElementById("goto_jobs_view");
			on_goto.onclick = on_goto_jobs;
		})();
	</script>-->
</body>
</html>
