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
	<title>Viewing a job</title>
</head>
<body>
	<?php include('top_bar.php'); ?>
	<noscript>
	It seems javascript isn't enabled. This website uses it for
	many fuctionalities (some navigation buttons, sorting and so on),
	so please enable javascript if you want to have full functionality !
	</noscript>
	<?php
		// Getting $job and $status
		$params = _test2(get_GET_parameters(array('job')));
		$job = $params['job'];
		// Getting actual content
		$raw = _test2(fimo_get_raw_output(session_id(), $job));
		// Formatting output for display
		// Note: $raw[0] = '#', so we eliminate it
		$raw[0] = '';
		$output = split_into_matrix($raw);
		$titles = $output[0];
		$data = array_slice($output, 1);
	?>
	<p>
		Select filters:<br/>
		Score limit: <input type="text" id="text_score"><br/>
		p-value limit:
		<select id="select_p-value">
		<option value="0.0001">10⁻⁴</option>
		<option value="0.00001">10⁻⁵</option>
		<option value="0.000001">10⁻⁵</option>
		<option value="0.0000001">10⁻⁷</option>
		</select><br/>
<!--		q-value limit:-->
		<select id="select_q-value" hidden="true">
		<option value="0.0001">10⁻⁴</option>
		<option value="0.00001">10⁻⁵</option>
		<option value="0.000001">10⁻⁵</option>
		<option value="0.0000001">10⁻⁷</option>
		</select><br/>
		<button id="filter_button" onclick="filter_button_onclick()">Filter !</button>
	</p>
	<p>
		Sort by:
		<select id="select_sort" onchange="sort_onchange()">
		<option value="42">Don't sort</option>
		<option value="score">score</option>
		<option value="p-value">p-value</option>
<!--		<option value="q-value">q-value</option>-->
		<option value="occurrence">gene occurrence</option>
		</select>
		<select id="select_order">
		<option value="<">from smallest to biggest</option>
		<option value=">">from biggest to smallest</option>
		</select>
		<button id="sort_button" onclick="sort_button_onclick()">Sort !</button>
	</p>
	<p>
<!--		<button id="back_to_jobs_button" onclick="back_to_jobs_button_onclick()">Back to the jobs</button>-->
		<button id="download_button" onclick="download_button_onclick()">Download output</button>
		<button id="nice_view_button" onclick="nice_view_button_onclick()">Visualisation</button>
	</p>
	<table id="output_table">
	<tr><th>
	<?php
		echo implode('</th><th>', $titles);
	?>
	</th></tr>
	<?php
		$i = 0;
		foreach ($data as $line)
		{
			echo "<tr id=\"$i\">";
			foreach ($line as $el)
			{
				echo "<td>$el</td>";
			}
			echo '</tr>';
			$i++;
		}
	?>
	</table>
	<script type="text/javascript">
		var g_n_entries = <?php echo sizeof($output) - 1;?>;
		var g_download_name="fimo_output.txt";
		
		(function(){
			var select_order = document.getElementById("select_order");
			select_order.disabled = true;

			if (!String.prototype.ltrim)
			{
				String.prototype.ltrim = function() { return this.replace(/^\s+/, ''); };
			}
		})();

		function get_score(id)
		{
			var parent = document.getElementById(id);
			var cell = parent.childNodes[5];
			var score = cell.firstChild.data;
			
			return parseFloat(score);
		}
		function get_pvalue(id)
		{
			var parent = document.getElementById(id);
			var cell = parent.childNodes[6];
			var pvalue = cell.firstChild.data;

			return parseFloat(pvalue);
		}
		function get_qvalue(id)
		{
			var parent = document.getElementById(id);
			var cell = parent.childNodes[7];
			var qvalue = cell.firstChild.data;

			return parseFloat(qvalue);
		}
		function get_genename(id)
		{
			var parent = document.getElementById(id);
			var cell = parent.childNodes[9];
			var gene = cell.firstChild.data;

			return gene;
		}
		
		function hide_entries_by(l_score, l_pvalue, l_qvalue)
		{
			for (var i = 0; i < g_n_entries; i++)
			{
				var parent = document.getElementById(i);
				var score = get_score(i);
				var pvalue = get_pvalue(i);
				var qvalue = get_qvalue(i);
				parent.hidden = (score < l_score || pvalue > l_pvalue/* || qvalue > l_qvalue*/);
			}
		}
		function filter_button_onclick()
		{
			var score = document.getElementById("text_score").value;
			var pvalue = document.getElementById("select_p-value").value;
			var qvalue = document.getElementById("select_q-value").value;

/*			if (isNaN(score))
			{
				score = 42;
			}*/

			hide_entries_by(score, pvalue, qvalue);
		}
		
		function swap_rows(i1, i2)
		{
			var r1 = document.getElementById(i1);
			var r2 = document.getElementById(i2);

			var t = r1.innerHTML;
			r1.innerHTML = r2.innerHTML;
			r2.innerHTML = t;
		}
		function get_entries()
		{
			var ret = [];
			
			for (var i = 0; i < g_n_entries; i++)
				ret[i] = document.getElementById(i);

			return ret;
		}
		function get_scores()
		{
			var ret = [];

			for (var i = 0; i < g_n_entries; i++)
				ret[i] = get_score(i);

			return ret;
		}
		function get_pvalues()
		{
			var ret = [];

			for (var i = 0; i < g_n_entries; i++)
				ret[i] = get_pvalue(i);

			return ret;
		}
		function get_qvalues()
		{
			var ret = [];

			for (var i = 0; i < g_n_entries; i++)
				ret[i] = get_qvalue(i);

			return ret;
		}
		function get_genenames()
		{
			var ret = [];

			for (var i = 0; i < g_n_entries; i++)
				ret[i] = get_genename(i);

			return ret;
		}
		function get_occurrences()
		{
			var ret = {};

			for (var i = 0; i < g_n_entries; i++)
			{
				var name = get_genename(i);

				if (ret[name])
					ret[name] += 1;
				else
					ret[name] = 1;
			}

			return ret;
		}
		function array_unique(multi)
		{
			var ret = [];

			for (var i = 0; i < g_n_entries; i++)
			{
				if (ret[ret.length - 1] != multi[i])
					ret.push(multi[i]);
			}

			return ret;
		}
		function array_indicesof(el, input)
		{
			var ret = [];

			for (var i = 0; i < g_n_entries; i++)
			{
				if (el == input[i])
					ret.push(i);
			}

			return ret;
		}
		function get_substitutions(unsorted, orderfunc)
		{
			var ret = [];
			var sorted = unsorted.slice(0);
			sorted.sort(orderfunc);
			sorted = array_unique(sorted);

			for (var i = 0; i < sorted.length; i++)
				ret = ret.concat(array_indicesof(sorted[i], unsorted));

			return ret;
		}
		function sort_onchange()
		{
			var select_sort = document.getElementById("select_sort");
			var select_order = document.getElementById("select_order");

			switch (select_sort.value)
			{
			case "score":
				select_order.value = ">";
				break;
			case "p-value":
				select_order.value = "<";
				break;
			case "q-value":
				select_order.value = "<";
				break;
			case "occurrence":
				select_order.value = ">";
				break;
			default:
				break;
			}
			select_order.disabled = (select_sort.value == "42");
		}
		function sort_button_onclick()
		{
			var select_sort = document.getElementById("select_sort").value;
			var select_order = document.getElementById("select_order").value;

			var entries = get_entries();
			switch (select_sort)
			{
			case "score":
				var to_order = get_scores();
				break;
			case "p-value":
				var to_order = get_pvalues();
				break;
			case "q-value":
				var to_order = get_qvalues();
				break;
			case "occurrence":
				var to_order = get_occurrences();
				break;
			default:
				return;
			}
			if (select_sort == "occurrence")
			{
				if (select_order == "<")
					var orderfunc = function(a, b) { return to_order[a] - to_order[b]; };
				else if (select_order == ">")
					var orderfunc = function(a, b) { return to_order[b] - to_order[a]; };
				else
					return;

				// Getting keys from 'to_order' as 'genes' ;
				// array necessary when trying to sort
				var genes = [];
				for (var gene in to_order)
					genes.push(gene);
				var genes_unsorted = get_genenames();
				genes.sort(orderfunc);

				var substitutions = [];
				for (var i = 0; i < genes.length; i++)
					substitutions = substitutions.concat(array_indicesof(genes[i], genes_unsorted));
			}
			else
			{
				if (select_order == "<")
					var orderfunc = function(a, b) { return a - b; };
				else if (select_order == ">")
					var orderfunc = function(a, b) { return b - a; };
				else
					return;

				var substitutions = get_substitutions(to_order, orderfunc);
			}

			var entries_new = [];
			for (var i = 0; i < g_n_entries; i++)
				entries_new.push(entries[substitutions[i]].innerHTML);
			for (var i = 0; i < g_n_entries; i++)
				entries[i].innerHTML = entries_new[i];
		}
		
		function is_str_empty(str)
		{
			return (!str || 0 === str.length);
		}
		function is_str_not_empty(str)
		{
			return !is_str_empty(str);
		}
		function str_ltrim(str)
		{
			return str.ltrim();
		}
		function get_output_table()
		{
			var table = document.getElementById("output_table");
			var html = table.innerHTML.split("\n").map(str_ltrim).join("");

			var headers = [];
			var content = [];
			var lines = [];
			
			var th_bounds = /<th>(.*?)<\/th>/g;
			var tr_bounds = /<tr\sid=["'][0-9]+["']>(.*?)<\/tr>/g;
			var td_bounds = /<td>(.*?)<\/td>/g;

			var match;
			// First getting the headers
			while (match = th_bounds.exec(html))
			{
				headers.push(match[1]);
			}
			// Then dividing the rest of the table into lines
			while (match = tr_bounds.exec(html))
			{
				lines.push(match[1]);
			}
			// Before we put the whole thing into content
			for (var i = 0; i < lines.length; i++)
			{
				var temp = [];

				while (match = td_bounds.exec(lines[i]))
				{
					temp.push(match[1]);
				}

				content.push(temp);
			}

			return [headers, content];
		}
		function download_button_onclick()
		{
			var table = get_output_table();
			var content = [table[0]].concat(table[1]);
			var output = "#";

			for (var i = 0; i < content.length; i++)
			{
				output += content[i].join("\t") + "\n";
			}

			var blob = new Blob([output], {type:'text/plain'});
			var link = document.createElement("a");
			link.download = g_download_name;
			link.innerHTML = "Download as file";
			link.href = window.URL.createObjectURL(blob);
			link.onclick = function(event) { document.body.removeChild(event.target); };
			link.style.display = "none";
			document.body.appendChild(link);
			link.click();
		}
		
		function back_to_jobs_button_onclick()
		{
			window.location = "<?php echo "http://$LAB_ADDRESS/view_jobs.php"; ?>";
		}
		
		function nice_view_button_onclick()
		{
			window.location = "<?php echo "http://$LAB_ADDRESS/visualisation.php?job=$job"; ?>";
		}
	</script>
</body>
</html>
