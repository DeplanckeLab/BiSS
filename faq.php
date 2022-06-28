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
		<b>How do we get the transcription factors ?</b><br/>
		We first use <a href="http://meme.nbcr.net/meme/fimo-intro.html" target="_blank">FIMO</a> to screen, and then modify its output by adding a <b>binding site</b> column, those being found by querying a database we created.
	</p>
	<p>
		<b>What sequence type can I submit ?</b><br/>
		Sequences should be in FASTA format. You can input one sequence without header, and it will be named <b>user input</b>, but it has still to be in FASTA format.<br/>
		Also, to prevent misuse (see below) of this website, and overload of the server, length is limited to 10000 base pairs per sequence.
	</p>
</body>
</html>
