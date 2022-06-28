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
	<noscript>
	It seems javascript isn't enabled. This website uses it for
	many fuctionalities (some navigation buttons, sorting and so on),
	so please enable javascript if you want to have full functionality !<br/>
	This page in particular cannot yield good content with javascript disabled !
	</noscript>
	<?php
trigger_error("test", E_USER_WARNING);
		// Getting $job and $status
		$params = _test2(get_GET_parameters(array('job')));
		$job = $params['job'];
		// Getting the sequences
		$raw_input = _test2(fimo_get_input(session_id(), $job));
		$input = format_sequence_into_sequences($raw_input);
		// Getting the fimo output
		$raw_output = _test2(fimo_get_raw_output(session_id(), $job));
		// Formatting output for display
		// We won't use the titles, though
		$output = split_into_matrix($raw_output);
		$titles = $output[0];
		$data = array_slice($output, 1);
		// The sequence names
		$input_keys = array_keys($input);
	?>
	<fieldset id="nice_filters">
		<legend>Filters</legend>
		Score limit: <input type="text" id="text_score"><br/><br/>
		p-value limit:
		<select id="select_p-value">
		<option value="0.001">10⁻⁴ (default)</option>
		<option value="0.0001">10⁻⁵</option>
		<option value="0.00001">10⁻⁶</option>
		<option value="0.000001">10⁻⁷</option>
		</select><br/>
<!--		q-value limit: -->
		<select id="select_q-value" hidden=true>
		<option value="1">No limit</option>
		<option value="0.01">0.01</option>
		<option value="0.001">0.001</option>
		<option value="0.0001">0.0001</option>
		</select><br/>
		<button id="filter_button" onclick="filter_button_onclick()">Filter !</button>
	</fieldset>
	<fieldset id="nice_colours">
		<legend>Colours</legend>
		Colour according to
		<select id="select_colour_method" onchange="change_colour_method()">
		<option value="random">(random)</option>
		<option value="score">score</option>
		<option value="p-value">p-value</option>
<!--		<option value="q-value">q-value</option>-->
		</select>
	</fieldset>
	<fieldset id="nice_other">
	<legend>Other operations</legend>
		Show only sequence
		<select id="select_only_sequence" onchange="select_only_sequence()">
		<option value="all">(all sequences)</option>
		<?php
			foreach ($input_keys as $key)
			{
				echo "<option value=\"$key\">$key</option>";
			}
		?>
		</select><br/><br/>
		Show only transcription factor
		<select id="select_tfs" onchange="select_tfs()">
		<option value="all">(all transcription factors)</option>
		<?php
			$tfs = array();
			foreach ($data as $tf)
			{
				if (!in_array($tf[9], $tfs))
				{
					$tfs[] = $tf[9];
				}
			}
			foreach ($tfs as $tf)
			{
				echo "<option value=\"$tf\">$tf</option>";
			}
		?>
		</select><br/><br/>
	<button onclick="get_raw_view()">Raw output</button>
	</fieldset>
	<?php
		function cmp($a, $b)
		{
			return ($data[$a][6] < $data[$b][6])? -1 : 1;
		}
		$matches = get_matches($input_keys, $data);
		// We display content (some is hidden) for the user
		// and for us to process through javascript.
		foreach ($input_keys as $key)
		{
			usort($matches[key], cmp);
			$n_matched = sizeof($matches[$key]);
			$patterns = array();
			$starts = array();
			$stops = array();
			$strands = array();
			$scores = array();
			$pvalues = array();
			$qvalues = array();
			$sequences = array();
			for ($i = 0; $i < $n_matched; $i++)
			{
				$patterns[] = $data[$matches[$key][$i]][9];
				$starts[] = $data[$matches[$key][$i]][2];
				$stops[] = $data[$matches[$key][$i]][3];
				$strands[] = $data[$matches[$key][$i]][4];
				$scores[] = $data[$matches[$key][$i]][5];
				$pvalues[] = $data[$matches[$key][$i]][6];
				$qvalues[] = $data[$matches[$key][$i]][7];
				$sequences[] = $data[$matches[$key][$i]][8];
			}

			echo "<p id=\"p_$key\">\n$key:\n";
			echo "<div hidden=\"true\">\n";
			echo "<div id=\"input_$key\">" . $input[$key] . "</div>\n";
			echo "<div id=\"patterns_$key\">" . implode($patterns, ',') . "</div>\n";
			echo "<div id=\"starts_$key\">" . implode($starts, ',') . "</div>\n";
			echo "<div id=\"stops_$key\">" . implode($stops, ',') . "</div>\n";
			echo "<div id=\"strands_$key\">" . implode($strands, ',') . "</div>\n";
			echo "<div id=\"scores_$key\">" . implode($scores, ',') . "</div>\n";
			echo "<div id=\"p-values_$key\">" . implode($pvalues, ',') . "</div>\n";
			echo "<div id=\"q-values_$key\">" . implode($qvalues, ',') . "</div>\n";
			echo "<div id=\"sequences_$key\">" . implode($sequences, ',') . "</div>\n";
			echo "</div>\n";
			echo '<canvas id="canvas_' . $key . '"' . "></canvas>\n";
			echo "<fieldset id=\"output_$key\">transcription factor:<br/>sequence:<br/>p-value:</fieldset>\n";
			echo "</p>\n";
		}
	?>
	<script type="text/javascript">
		var g_job = "<?php echo $job; ?>";
		var g_n_entries = <?php echo sizeof($input);?>;
		var g_entries = ["<?php echo implode('", "', $input_keys);?>"];
		var g_sequence_lengths = [];
		var g_sequence_units = [];
		var g_rects = [];

		var g_width_factor = 0.8;
		var g_width = window.innerWidth;

		var g_x_offset_factor = 0.1;
		var g_x_offset = window.innerWidth * g_x_offset_factor;
		var g_canvas_width = g_width;
		var g_canvas_default_height = 200;
		var g_sequence_lines_y = [];
		var g_sequence_line_width = g_width * g_width_factor;
		var g_sequence_line_height = 5;
		var g_sequence_text_height = 10;
		var g_tfbox_height = 10;
		var g_strand_text_x = 50;

		var g_canvas_background_colour = "#FFFFFF";
		var g_sequence_line_colour = "#000000";
		var g_sequence_text_colour = "#000000";

		var g_min_max_scores = [];

		// name should be "input", "patterns", "starts", "stops" or "strands"
		function get_sequence_data(id, divname)
		{
			var div = document.getElementById(divname + "_" + g_entries[id]);

			return div.textContent;
		}
		function get_sequence_length(id)
		{
			var seq = get_sequence_data(id, "input");

			return seq.length;
		}

		function draw_sequence_line(id, canvas, context)
		{
			var height = g_sequence_lines_y[id];

			context.fillStyle = g_sequence_line_colour;
			context.fillRect(g_x_offset, height, g_sequence_line_width, g_sequence_line_height);
		}

		function get_highest_power_of_10(n)
		{
			var i = 1;

			while (n / i >= 1)
			{
				i *= 10;
			}

			return i / 10;
		}
		function draw_bp(id, canvas, context)
		{
			var height = g_sequence_lines_y[id];
			var length = g_sequence_lengths[id];
			var pow10 = get_highest_power_of_10(length);

			// if length = 10xx, pow10 = 1000 ; we may want
			// to draw a more detailed scale than 0 and 1000.
			if (length - pow10 < pow10)
				pow10 /= 10;

			context.fillStyle = g_sequence_text_colour;
			context.font = g_sequence_text_height + "px Arial";

			var max = length / pow10;
			var t;
			for (var i = 0; i < max; i++)
			{
				t = i * pow10;
				context.fillText(t, g_x_offset + t * g_sequence_units[id], height);
			}
			context.fillText("+ strand:", g_x_offset - g_strand_text_x, height/* - g_sequence_text_height*/);
			context.fillText("-  strand:", g_x_offset - g_strand_text_x, height + g_sequence_text_height + 3);
		}

		function gen_random_colour()
		{
			var colour = "#";

			for (var i = 0; i < 6; i++)
			{
				colour += Math.floor(Math.random() * 16).toString(16);
			}

			return colour;
		}
		function p_inside(p, x, w)
		{
			return (x <= p && p <= x + w);
		}
		function ranges_intersect(r1, r2)
		{
			if (!(r1[6] && r2[6]))
			{
				return false;
			}
			else if (r1[5]*r2[5] < 0)
			{
				return false;
			}
			else if (p_inside(r1[0], r2[0], r2[2]) || p_inside(r2[0], r1[0], r1[2]))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		function find_free_space(id, rects, drawn)
		{
			var n_drawn = drawn.length;

			// Every rect which could intersect ;
			// other strand rects are not neighbours
			var neighbours = [];
			for (var i = 0; i < n_drawn; i++)
			{
				if (ranges_intersect(rects[id], rects[drawn[i]]))
				{
					if (id != i)
					{
						neighbours.push(i);
					}
				}
			}

			var broke = true;
			var n_neighbours = neighbours.length;
			while (broke)
			{
				broke = false;
				for (var i = 0; i < n_neighbours; i++)
				{
					if (rects[id][1] == rects[neighbours[i]][1])
					{
						rects[id][1] += g_tfbox_height;
						broke = true;
						break;
					}
				}
			}
		}
		function calculate_rects(id, recalculate)
		{
			var starts = get_sequence_data(id, "starts").split(",");
			var stops = get_sequence_data(id, "stops").split(",");
			var strands = get_sequence_data(id, "strands").split(",");
			var n_matched = starts.length;
			var drawn = [];

			// First pass ; y is not that important yet
			for (var i = 0; i < n_matched; i++)
			{
				var dx = stops[i] - starts[i];
				var x = g_sequence_units[id] * starts[i];
				var y = 0;
				var w = g_sequence_units[id] * dx;
				var h = g_tfbox_height;
				var colour = gen_random_colour();
				var strand = (strands[i] == "+")? 1 : -1;
				var shown = true;

				if (recalculate)
				{
					var rect = g_rects[id][i];
					g_rects[id][i] = [x, y, w, h, rect[4], strand, rect[6]];
				}
				else
				{
					g_rects[id][i] = [x, y, w, h, colour, strand, shown];
				}
			}

			// Second pass: finding room for the y
			for (var i = 0; i < n_matched; i++)
			{
				find_free_space(i, g_rects[id], drawn);
				drawn.push(i);
			}

			// Third pass: correction of x (+offset),
			// and y (according to strand)
			for (var i = 0; i < n_matched; i++)
			{
				g_rects[id][i][0] += g_x_offset;

				var strand = g_rects[id][i][5];
				var y = strand * g_rects[id][i][1];
				if (strand > 0)
				{
					y += g_tfbox_height + g_sequence_text_height - 3;
				}
				else
				{
					y -= g_sequence_line_height;
				}
				g_rects[id][i][1] = y;
			}
		}
		function draw_tfboxes(id, canvas, context)
		{
			var n_matched = g_rects[id].length;
			var y = g_sequence_lines_y[id];

			for (var i = 0; i < n_matched; i++)
			{
				var rect = g_rects[id][i];

				if (rect[6])
				{
					context.fillStyle = rect[4];
					context.fillRect(rect[0], y - rect[1], rect[2], rect[3]);
				}
			}
		}

		function get_canvas_height(id)
		{
			var n_rects = g_rects[id].length;
			var highest = 0;
			var lowest = 0;

			for (var i = 0; i < n_rects; i++)
			{
				if (g_rects[id][i][1] > highest)
				{
					highest = g_rects[id][i][1];
				}
				if (g_rects[id][i][1] < lowest)
				{
					lowest = g_rects[id][i][1];
				}
			}

			return [highest - lowest + g_tfbox_height, highest];
		}
		function min(input)
		{
			return Math.min.apply(null, input);
		}
		function max(input)
		{
			return Math.max.apply(null, input);
		}
		function get_min_max(id)
		{
			var scores = get_sequence_data(id, "scores").split(",");
			var pvalues = get_sequence_data(id, "p-values").split(",");
			var qvalues = get_sequence_data(id, "q-values").split(",");
			var ret = {};

			// No good functional tools to do that in a short way... :(
			var scores2 = [];
			var pvalues2 = [];
			var qvalues2 = [];
			for (var i = 0; i < scores.length; i++)
			{
				if (g_rects[id][i][6])
				{
					scores2.push(scores[i]);
					pvalues2.push(pvalues[i]);
					qvalues2.push(qvalues[i]);
				}
			}

			ret["score"] = [min(scores2), max(scores2)];
			ret["p-value"] = [min(pvalues2), max(pvalues2)];
			ret["q-value"] = [min(qvalues2), max(qvalues2)];

			ret["score"][2] = ret["score"][1] - ret["score"][0];
			ret["p-value"][2] = ret["p-value"][1] - ret["p-value"][0];
			ret["q-value"][2] = ret["q-value"][1] - ret["q-value"][0];

			return ret;
		}
		(function(){
			for (var i = 0; i < g_n_entries; i++)
			{
				var canvas = document.getElementById("canvas_" + g_entries[i]);
				var context = canvas.getContext("2d");

				canvas.width = g_canvas_width;
				canvas.height = g_canvas_default_height;
				canvas.number = i;
				canvas.onmousemove = canvas_mouse_move;

				g_sequence_lengths[i] = get_sequence_length(i);
				g_sequence_units[i] = g_sequence_line_width / g_sequence_lengths[i];
				g_rects[i] = [];
				calculate_rects(i, false);
				height_highest = get_canvas_height(i);
				canvas.height = height_highest[0];
				g_sequence_lines_y[i] = height_highest[1];

				g_min_max_scores[i] = get_min_max(i);
			}
			
			refresh_canvas();
		})();

		function refresh_canvas()
		{
			for (var i = 0; i < g_n_entries; i++)
			{
				var canvas = document.getElementById("canvas_" + g_entries[i]);
				var context = canvas.getContext("2d");

				context.fillStyle = g_canvas_background_colour;
				context.fillRect(0, 0, canvas.width, canvas.height);

				draw_sequence_line(i, canvas, context);
				draw_bp(i, canvas, context);
				draw_tfboxes(i, canvas, context);
			}
		}

		function get_colour_from(rmask, gmask, bmask, intensity)
		{
			var ret = "#";

			ret += ("00" + Math.floor(rmask*intensity).toString(16)).slice(-2);
			ret += ("00" + Math.floor(gmask*intensity).toString(16)).slice(-2);
			ret += ("00" + Math.floor(bmask*intensity).toString(16)).slice(-2);

			return ret;
		}
		function get_colour_for(intensity)
		{
/*			Green to red gradient.	*/
			var ret = "";

/*			ret += ("00" + Math.floor(255*intensity).toString(16)).slice(-2);
			ret += ("00" + Math.floor(255*(1-intensity)).toString(16)).slice(-2);
			ret += "00";*/
/*			Blue to yellow gradient.	*/
			ret += ("00" + Math.floor(255*(1-intensity)).toString(16)).slice(-2);
			ret += ret;
			ret += ("00" + Math.floor(255*intensity).toString(16)).slice(-2);
/*			Black to red gradient.	*/
/*			ret += ("00" + Math.floor(255*intensity).toString(16)).slice(-2);
			ret += "0000";*/

			return "#" + ret;
		}
		function change_colour_method()
		{
			var select = document.getElementById("select_colour_method");
			var method = select.value;

			for (var i = 0; i < g_n_entries; i++)
			{
				var scores = get_sequence_data(i, "scores").split(",");
				var pvalues = get_sequence_data(i, "p-values").split(",");
				var qvalues = get_sequence_data(i, "q-values").split(",");
				var n_matched = g_rects[i].length;

				for (var j = 0; j < n_matched; j++)
				{
					switch (method)
					{
					case "random":
						g_rects[i][j][4] = gen_random_colour();
						break;
					case "score":
						var minimum = g_min_max_scores[i]["score"][0];
						var normalised = (scores[j]-minimum)/g_min_max_scores[i]["score"][2];
						g_rects[i][j][4] = get_colour_for(normalised);
						break;
					case "p-value":
						var minimum = g_min_max_scores[i]["p-value"][0];
						var normalised = (pvalues[j]-minimum)/g_min_max_scores[i]["p-value"][2];
						g_rects[i][j][4] = get_colour_for(normalised);
						break;
					case "q-value":
						var minimum = g_min_max_scores[i]["q-value"][0];
						var normalised = (qvalues[j]-minimum)/g_min_max_scores[i]["q-value"][2];
						g_rects[i][j][4] = get_colour_for(normalised);
						break;
					default:
						break;
					}
				}
			}

			refresh_canvas();
		}

		function hide_entries_by(l_score, l_pvalue, l_qvalue)
		{
			if (l_score.length < 1)
			{
				l_score = -10000;
			}
			else
			{
				l_score = parseFloat(l_score);
			}

			for (var i = 0; i < g_n_entries; i++)
			{
				var scores = get_sequence_data(i, "scores").split(",");
				var pvalues = get_sequence_data(i, "p-values").split(",");
				var qvalues = get_sequence_data(i, "q-values").split(",");
				var n_matched = g_rects[i].length;

				for (var j = 0; j < n_matched; j++)
				{
					g_rects[i][j][6] = (scores[j] > l_score && pvalues[j] < l_pvalue/* && qvalues[j] < l_qvalue*/);
				}
			}
		}
		function filter_button_onclick()
		{
			var score = document.getElementById("text_score").value;
			var pvalue = document.getElementById("select_p-value").value;
			var qvalue = document.getElementById("select_q-value").value;

			if (isNaN(score))
			{
				alert("Please enter a number in the 'Score limit:' field.");
				return;
			}
			hide_entries_by(score, parseFloat(pvalue), parseFloat(qvalue));

			for (var i = 0; i < g_n_entries; i++)
			{
				var canvas = document.getElementById("canvas_" + g_entries[i]);

				calculate_rects(i, true);
				height_highest = get_canvas_height(i);
				canvas.height = height_highest[0];
				g_sequence_lines_y[i] = height_highest[1];
				g_min_max_scores[i] = get_min_max(i);
			}

			// The hidden entries may have been minima, so we
			// use this method, which refreshes canvas anyway
			change_colour_method();
		}
		
		function select_only_sequence()
		{
			var select = document.getElementById("select_only_sequence");
			var sequence = select.value;

			var ps = [];
			var canvases = [];
			var outputs = [];

			for (var i = 0; i < g_n_entries; i++)
			{
				ps.push(document.getElementById("p_" + g_entries[i]));
				canvases.push(document.getElementById("canvas_" + g_entries[i]));
				outputs.push(document.getElementById("output_" + g_entries[i]));
			}

			if (sequence == "all")
			{
				for (var i = 0; i < g_n_entries; i++)
				{
					ps[i].hidden = false;
					canvases[i].hidden = false;
					outputs[i].hidden = false;
				}
			}
			else
			{
				for (var i = 0; i < g_n_entries; i++)
				{
					ps[i].hidden = (sequence == g_entries[i])? false : true;
					canvases[i].hidden = (sequence == g_entries[i])? false : true;
					outputs[i].hidden = (sequence == g_entries[i])? false : true;
				}
			}
		}

		function hide_entries_by_name(name)
		{
			for (var i = 0; i < g_n_entries; i++)
			{
				var names = get_sequence_data(i, "patterns").split(",");
				var n_matched = g_rects[i].length;

				for (var j = 0; j < n_matched; j++)
				{
					g_rects[i][j][6] = (names[j] == name);
				}
			}
		}
		function show_all_entries_again()
		{
			for (var i = 0; i < g_n_entries; i++)
			{
				var n_matched = g_rects[i].length;

				for (var j = 0; j < n_matched; j++)
				{
					g_rects[i][j][6] = true;
				}
			}
		}
		function select_tfs()
		{
			var select = document.getElementById("select_tfs");
			var tf = select.value;

			if (tf == "all")
			{
				show_all_entries_again();
			}
			else
			{
				hide_entries_by_name(select.value);
			}

/*			To avoid bugs : resetting the filters.	*/
			var score = document.getElementById("text_score");
			var pvalue = document.getElementById("select_p-value");
			var qvalue = document.getElementById("select_q-value");

			score.value = "";
			pvalue.value = "1";
			qvalue.value = "1";

			for (var i = 0; i < g_n_entries; i++)
			{
				var canvas = document.getElementById("canvas_" + g_entries[i]);

				calculate_rects(i, true);
				height_highest = get_canvas_height(i);
				canvas.height = height_highest[0];
				g_sequence_lines_y[i] = height_highest[1];
				g_min_max_scores[i] = get_min_max(i);
			}
			refresh_canvas();
		}

		function canvas_mouse_move(event)
		{
			var e = event || window.event;
			var rect = e.target.getBoundingClientRect();
			var x = e.clientX - rect.left;
			var y = e.clientY - rect.top;
			var id = e.target.number;
			var output = document.getElementById("output_" + g_entries[id]);
		
			var on = -1;
			for (var i = 0; i < g_rects[id].length; i++)
			{
				rect = g_rects[id][i];
				if (!rect[6])
				{
					continue;
				}
				
				if (p_inside(x, rect[0], rect[2]) && p_inside(y, g_sequence_lines_y[id] - rect[1], rect[3]))
				{
					on = i;
				}
			}

			if (on > -1)
			{
				var names = get_sequence_data(id, "patterns").split(",");
				var seqs = get_sequence_data(id, "sequences").split(",");
				var pvalues = get_sequence_data(id, "p-values").split(",");
				var content = "";

				content += "transcription factor: " + names[on] + "<br/>";
				content += "sequence: " + seqs[on] + "<br/>";
				content += "p-value: " + pvalues[on];

				output.innerHTML = content;
			}
			else
			{
				output.innerHTML = "transcription factor:<br/>sequence:<br/>p-value:";
			}
		}
		function get_raw_view()
		{
			window.location = "http://<?php echo $LAB_ADDRESS; ?>/view_output.php?job=" + g_job;
		}
	</script>
</body>
</html>
