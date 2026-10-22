<?php
//	We convert a raw fasta file/string into an array where the headers are the
//	key and the values are the nucleotide sequence. Please note that if the
//	first sequence has no header (may be the case quite often, if just copying
//	content into the website's textbox, or that we want a quick run on only one
//	sequence), the sequence will get a default key of 0. Another function below
//	can reassign that key to another header if necessary (assign_headers).
//	Input:
//		$seq: the big string containing the sequences
//	Output:
//		An array of sequences, with their headers as keys
//	Example:
//		$raw_fasta = '	>seq1
//						ACCGTSAGATCWTA
//						CGTTCGTATT
//						>ablabla
//						TAGCTCNTATA';
//		$sequences = split_into_sequences(">seq1");
//		In this example, $sequences is
//			seq1	=>	ACCGTSAGATCWTACGTTCGTATT
//			ablabla	=>	TAGCTCNTATA
	function split_into_sequences($seq)
	{
		$seqs = array();
		$index = 0;
		$lines = explode("\n", $seq);
		foreach ($lines as $line)
		{
			$l = trim($line);
			if ($l === '')
			{
				continue;
			}
			if ($l[0] === '>')
			{
				$index = str_replace(' ', '_', $l);
				if (array_key_exists($index, $seqs))
				{
					exit('Two fasta headers (' . escape_html($index) . ') are the same ! Exiting...');
				}
				
				$seqs[$index] = '';
			}
			else
			{
				if (!array_key_exists($index, $seqs))
				{
					$seqs[$index] = '';
				}
				$seqs[$index] .= $l;
			}
		}
		return $seqs;
	}
	
//	Tests if each character from a raw fasta sequence is valid.
//	Input:
//		$fasta: the sequence of nucleic acids
//	Output:
//		false as soon as one character is invalid, else true
//	Example:
//		if (is_fasta_valid('acgtpstw')) do_something(...);
//	Remark:
//		A: Adenosine
//		C: Cytosine
//		G: Guanine
//		T: Thymine
//		N,S,W: Purines, Pyrimidines, Other
	function is_fasta_valid($fasta)
	{
		foreach (str_split(strtolower($fasta)) as $char)
		{
			if (strpos("acgtnsw", $char) === false)
			{
				return false;
			}
		}
		
		return true;
	}
	
//	Directly after having got the raw user input (text or file),
//	testing if there is anything at all.
//	Input:
//		$in: the raw user input
//	Output:
//		An empty string if everything is OK, else an error string.
//	Example:
//		_test(pre_check_input(''));
//		This will exit and yield a blank page with 'Empty sequence !' on it.
	function pre_check_input($in)
	{
		if (empty($in))
		{
			return 'Empty sequence !';
		}
		
		return '';
	}
	
//	This function permits to assign a default header to a sequence
//	if this sequence had no header (instead of having 0 as header).
//	Input:
//		$seqs: array of sequences where the keys are their headers
//	Output:
//		The same array, except that where 0 was a key, 'user input'
//		is the new key.
//	Example:
//		Let's have an array $seqs where
//			0			=>	ACTGCSWAG
//			seq2		=>	CGATGCGGTATA
//		$seqs = assign_headers($seqs)
//		Our array now is
//			user input	=>	ACTGCSWAG
//			seq2		=>	CGATGCGGTATA
	function assign_headers($seqs)
	{
		if (array_key_exists(0, $seqs))
		{
			include('variables.php');
			
			$random_name = 'user_input';/*gen_random_sequence($N_RANDOM_CHARS);*/
			$seqs['>' . $random_name] = $seqs[0];
			
			unset($seqs[0]);
		}

		return $seqs;
	}
	
//	This function combines two above functions :
//	First it takes a raw sequences string, and makes sequences
//	out of it, then it assigns 'user input' to the missing header
//	if this were to be the case.
//	Input:
//		$raw: the raw sequence string
//	Output:
//		An array of sequences where the keys are their headers
//	Example:
//		$raw_content = 'ACGACTTACTGAC
//						CAGTCATSTG
//						>seq1
//						ACGTACTGACTATASWAG';
//		$sequences = format_sequence_into_sequences($raw_content);
//		Now we have an array $sequences where
//			user input	=>	ACGACTTACTGACCAGTCATSTG
//			seq1		=>	ACGTACTGACTATASWAG
	function format_sequence_into_sequences($raw)
	{
		$assigned = assign_headers(split_into_sequences($raw));
		return $assigned;
	}
	
	function check_sequences($seqs)
	{
		include('variables.php');
		$keys = array_keys($seqs);
		
		if (empty($keys))
		{
			return 'There aren\'t any sequences !';
		}
		
		foreach ($keys as $key)
		{
			$name = escape_html($key);

			if (empty($seqs[$key]))
			{
				return "A sequence ($name) is empty !";
			}
			
			if (!is_fasta_valid($seqs[$key]))
			{
				$temp = escape_html($seqs[$key]);
				return "A sequence ($name) is not in fasta format !<br/><br/>$temp";
			}
			
			if (strlen($seqs[$key]) > $MAX_FASTA_LENGTH)
			{
				return "A sequence ($name) is longer than the maximum allowed ! ($MAX_FASTA_LENGTH)";
			}
		}
		
		return '';
	}
	
	function launch_fimo($id, $job, $opts)
	{
		include('variables.php');
		$path = "$UPDIR_PATH/$id";
		$input = "$path/$INPUT_FOLDER/$job";
		$output = "$path/$FIMO_FOLDER/$job";
		$db = "$DB_PATH/$DEFAULT_DB";
		$r1 = decoct($UPDIR_RIGHTS);
		$r2 = decoct($UPFILES_RIGHTS);
		$useless = implode(' ', $FIMO_USELESS);
		$log = "$path/$LOG_FOLDER/$job.log";
		$fimo_out = "$FIMO_TXT";
		$temp_fimo = "$TEMP_FIMO_ANNOTATED";
		$gene_db = "$DB_PATH/$GENE_ANNOTATIONS_DB";
		$fimo_tsv = "$FIMO_TSV";
		
		// Every argument is escaped, so that nothing can be injected in the shell
		$args = array($id, $job, $path, $input, $output, $db, $r1, $r2, $useless, $log, $fimo_out, $temp_fimo, $gene_db, $opts, $fimo_tsv);
		$script = escapeshellarg("$BASE_ADDRESS/run_fimo.sh");
		$args = implode(' ', array_map('escapeshellarg', $args));
		$log = escapeshellarg($log);

		exec("bash $script $args > $log 2>&1 &");
	}
	
	function fimo_postsetup($id, $job)
	{
		include('variables.php');
		$path = "$UPDIR_PATH/$id";
		$output = "$path/$FIMO_FOLDER/$job";
		
		if (!chmod($output, $UPDIR_RIGHTS))
		{
			//!\ Not always a good idea to give the structure of the website /!\\
			return "Error: Couldn't change permissions on $output !";
		}

		$fimo_files = get_folder_content("$output");
		foreach ($fimo_files as $file)
		{
			if (in_array($file, $FIMO_USELESS))
			{
				if (!unlink("$output/$file"))
				{
					//!\ Not always a good idea to give the structure of the website /!\\
					return "Error: Couldn't remove (useless) file $output/$file !";
				}
			}
			else
			{
				if (!chmod("$output/$file", $UPFILES_RIGHTS))
				{
					//!\ Not always a good idea to give the structure of the website /!\\
					return "Error: Couldn't change permissions on $output/$file !";
				}
			}
		}
	}
	
	function get_jobs($id)
	{
		include('variables.php');
		$path = "$UPDIR_PATH/$id";

		if (empty($id))
		{
			return array('Error: No session ID !', array());
		}

		if (!file_exists($path))
		{
			return array('', array());
		}

		$content = get_folder_content("$path/$INPUT_FOLDER");
		$content = array_filter($content, function($in) { return !strpos($in, ".filename"); });
		return array('', $content);
	}
	
	function fimo_get_raw_output($id, $job)
	{
		include('variables.php');
		$path = "$UPDIR_PATH/$id/$FIMO_FOLDER";

		if (!file_exists("$path/$job/$FIMO_TXT"))
		{
			return array("Error: Output for job $job not found !", '');
		}
		$content = file_get_contents("$path/$job/$FIMO_TXT");
		
		// Removing the trailing newline (if any), so that
		// split_into_matrix doesn't yield an empty last line
		$content = rtrim($content, "\n");
		
		return array('', $content);
	}
?>
