<?php
//	Simple utility function to check if there is an error to an instruction ;
//	if no error, the instruction has to be such that a null-string is returned.
//	Input:
//		$error: the error string returned by the instruction we test
//	Output:
//		true if there is an error, false if there isn't
//	Example:
//		$is_there_an_error = has_error(some_funky_function(...));
	function has_error($error)
	{
		return (strlen($error) > 0);
	}

//	Shortcut for recurring tests in the style of the above ; if from the above
//	we get an error, the program exits, printing the error string to the user.
//	Input:
//		$trial: the instruction we want to test
//	Output:
//		None, the program just exits if there is an error
//	Example:
//		_test(some_funky_function(...));
	function _test($trial)
	{
		if (has_error($trial))
		{
			exit($trial);
		}
	}

	function _test2($trial)
	{
		$error = array_shift($trial);
		if (has_error($error))
		{
			exit($error);
		}
		else
		{
			if (sizeof($trial) == 1)
			{
				return $trial[0];
			}
			else
			{
				return $trial;
			}
		}
	}

//	This function is used to get determine if some fasta content
//	was given from a text box or from a fasta file, and yields the
//	content back. The priorities are:
//	-	If there is no file uploaded, we assume the content of the
//		text box.
//	-	Else, we first check if the file indeed has a name, and if
//		it has a .fa or .fasta extension ; if yes, we return the
//		content of the file, else we look at the text box again.
//	Input:
//		$seq: the content of the text box, a string
//		$file: the array which comes with a "file" input (in $_FILES)
//	Output:
//		The content of the text box or of a file
//	Example:
//		$raw_output = get_sequence($_POST['sequence'], $_FILES['fasta']);
	function get_sequence($seq, $file)
	{
		$name = $file['name'];
		$temp = $file['tmp_name'];
		$ext = '';
		$look_at_file = (file_exists($temp) && is_uploaded_file($temp));
		
		if ($look_at_file)
		{
			if (empty($temp))
				$look_at_file = false;
			else
				$ext = end(explode('.', $name));
			if ($ext != 'fa' && $ext != 'fasta')
				$look_at_file = false;
		}
		
		if ($look_at_file)
		{
			return array(file_get_contents($temp), $name);
		}
		else
		{
			$upname = 'unknown sequence';
			if (preg_match_all('/>\w*/', $seq, $matches))
			{
				$upname = implode(', ', $matches[0]);
			}
			return array($seq, $upname);
		}
	}
	
//	A simple utility function to get a random sequence of characters.
//	See $VALID_CHAR_LIST to see the range of characters.
//	Input:
//		$n_chars: the length of the sequence to generate
//	Output:
//		The string containing the sequence
	function gen_random_sequence($n_chars)
	{
		include('variables.php');
		$seq = '';
		
		for ($i = 0; $i < $n_chars; $i++)
		{
			$seq .= $VALID_CHAR_LIST[rand(0, strlen($VALID_CHAR_LIST) - 1)];
		}
		
		return $seq;
	}
	
	function create_context_for_user($id)
	{
		include('variables.php');
		$path = "$UPDIR_PATH/$id";

		if (empty($id))
		{
			return 'Error: No session ID !';
		}

		if (!file_exists($path))
		{
			if (!mkdir($path))
			{
				return "Error: Couldn't create directory $path !";
			}
			if (!chmod($path, $UPDIR_RIGHTS))
			{
				return "Error: Couldn't change permissions on $path !";
			}
		}

		if (!file_exists("$path/$INPUT_FOLDER"))
		{
			if (!mkdir("$path/$INPUT_FOLDER"))
			{
				return "Error: Couldn't create directory $path/$INPUT_FOLDER !";
			}
			if (!chmod("$path/$INPUT_FOLDER", $UPDIR_RIGHTS))
			{
				return "Error: Couldn't change permissions on $path/$INPUT_FOLDER !";
			}
		}

		if (!file_exists("$path/$FIMO_FOLDER"))
		{
			if (!mkdir("$path/$FIMO_FOLDER"))
			{
				return "Error: Couldn't create directory $path/$FIMO_FOLDER !";
			}
			if (!chmod("$path/$FIMO_FOLDER", $UPDIR_RIGHTS))
			{
				return "Error: Couldn't change permissions on $path/$FIMO_FOLDER !";
			}
		}
		
		if (!file_exists("$path/$LOG_FOLDER"))
		{
			if (!mkdir("$path/$LOG_FOLDER"))
			{
				return "Error: Couldn't create directory $path/$LOG_FOLDER !";
			}
			if (!chmod("$path/$LOG_FOLDER", $UPDIR_RIGHTS))
			{
				return "Error: Couldn't change permissions on $path/$LOG_FOLDER !";
			}
		}
	}
	
//	Once we have got our sequences, we want to create a temporary directory
//	and to create a file with the sequences inside it, so that FIMO can use
//	them. The temporary directory has the name of the session ID, and is
//	located in the place set by $UPDIR_PATH. The file created to store the
//	sequences is randomly generated. Please note that the content is then
//	put to upper case.
//	Input:
//		$id: the session ID, which will be the name of the directory
//		$seqs: the sequences to store in a file
//	Output:
//		An error string if something went wrong
	function move_input($id, $seqs, $oldname)
	{
		include('variables.php');
		$path = "$UPDIR_PATH/$id";
		
		if (empty($id))
		{
			return array('Error: No session ID !', '');
		}
		
		$filename = gen_random_sequence($N_RANDOM_CHARS);
		if (empty($filename))
		{
			return array('Error: Couldn\'t generate a random name for uploading the sequence !', '');
		}
		
		$data = '';
		$keys = array_keys($seqs);
		foreach ($keys as $key)
		{
			$data .= "$key\n";
			$data .= strtoupper($seqs[$key]) . "\n";
		}
		
		if (!file_put_contents("$path/$INPUT_FOLDER/$filename", $data))
		{
			return array("Error: Couldn't write the sequences to $path/$INPUT_FOLDER/$filename !", '');
		}
		if (!chmod("$path/$INPUT_FOLDER/$filename", $UPFILES_RIGHTS))
		{
			return array("Error: Couldn't change permissions on $path/$INPUT_FOLDER/$filename !", '');
		}
		if (!file_put_contents("$path/$INPUT_FOLDER/$filename.filename", $oldname))
		{
			return array("Error: Couldn't write the old filename to $path/$INPUT_FOLDER/$filename.filename !", '');
		}
		if (!chmod("$path/$INPUT_FOLDER/$filename.filename", $UPFILES_RIGHTS))
		{
			return array("Error: Couldn't change permissions on $path/$INPUT_FOLDER/$filename.filename !", '');
		}
		
		return array('', $filename);
	}
	
//	A function to delete any input by user with a certain ID, and
//	deleting his temporary directory also.
//	Input:
//		$id: the user session ID
//	Output:
//		An error string if something went wrong.
	function clean_user_input($id)
	{
		include('variables.php');
		$path = "$UPDIR_PATH/$id";
		
		// User input
		$input_files = get_folder_content("$path/$INPUT_FOLDER");
		foreach ($input_files as $file)
		{
			if (!unlink("$path/$INPUT_FOLDER/$file"))
			{
				return "Error: Couldn't delete file $path/$INPUT_FOLDER/$file !";
			}
		}
		if (!rmdir("$path/$INPUT_FOLDER"))
		{
			return "Error: Couldn't remove directory $path/$INPUT_FOLDER !";
		}
		
		$fimo_folders = get_folder_content("$path/$FIMO_FOLDER");
		foreach ($fimo_folders as $folder)
		{
			$fimo_files = get_folder_content("$path/$FIMO_FOLDER/$folder");
			foreach ($fimo_files as $file)
			{
				if (!unlink("$path/$FIMO_FOLDER/$folder/$file"))
				{
					return "Error: Couldn't delete file $path/$FIMO_FOLDER/$folder/$file !";
				}
			}
			if (!rmdir("$path/$FIMO_FOLDER/$folder"))
			{
				return "Error: Couldn't delete directory $path/$FIMO_FOLDER/$folder !";
			}
		}
		if (!rmdir("$path/$FIMO_FOLDER"))
		{
			return "Error: Couldn't remove directory $path/$FIMO_FOLDER !";
		}
		
		if (!rmdir("$path"))
		{
			return "Error: Couldn't remove directory $path !";
		}
		
		return '';
	}
	
	function delete_job($id, $job)
	{
		include('variables.php');
		$path = "$UPDIR_PATH/$id";
		
		// Deleting input
		if (!unlink("$path/$INPUT_FOLDER/$job"))
		{
			return "Error: Couldn't delete file $path/$FIMO_FOLDER/$job !";
		}
		if (!unlink("$path/$INPUT_FOLDER/$job.filename"))
		{
			return "Error: Couldn't delete fine $path/$FIMO_FOLDER/$job.filename !";
		}
		// Deleting log
		if (!unlink("$path/$LOG_FOLDER/$job.log"))
		{
			return "Error: Couldn't delete file $path/$LOG_FOLDER/$job.log !";
		}
		// Deleting fimo output
		if (!unlink("$path/$FIMO_FOLDER/$job.status"))
		{
			return "Error: Couldn't delete file $path/$FIMO_FOLDER/$job.status !";
		}
		// We won't assume fimo has finished running,
		// so we want to delete temporary files.
		$fimo_files = get_folder_content("$path/$FIMO_FOLDER/$job");
		foreach ($fimo_files as $file)
		{
			if (!unlink("$path/$FIMO_FOLDER/$job/$file"))
			{
				return "Error: Couldn't delete file $path/$FIMO_FOLDER/$job/$file !";
			}
		}
		if (!rmdir("$path/$FIMO_FOLDER/$job"))
		{
			return "Error: Couldn't delete directory $path/$FIMO_FOLDER/$job !";
		}

		return '';
	}
	
	function get_folder_content($path)
	{
		$files = scandir($path);
		// 0 is '.' and 1 is '..'
		unset($files[0]);
		unset($files[1]);
		
		return $files;
	}
	
	function get_GET_parameters($params)
	{
		$ret = array();
		foreach ($params as $param)
		{
			if (!isset($_GET[$param]))
			{
				return array("Error: Parameter $param not set through GET !", '');
			}
			$ret[$param] = $_GET[$param];
		}

		return array('', $ret);
	}
	
	function fimo_get_log($id, $job)
	{
		include('variables.php');
		$path = "$UPDIR_PATH/$id/$LOG_FOLDER";

		if (!file_exists("$path/$job.log"))
		{
			return array("Error: Log for job $job not found !", '');
		}
		$content = file_get_contents("$path/$job.log");

		return array('', $content);
	}
	
	function fimo_get_input($id, $job)
	{
		include('variables.php');
		$path = "$UPDIR_PATH/$id/$INPUT_FOLDER";

		if (!file_exists("$path/$job"))
		{
			return array("Error: Input for job $job not found !", '');
		}
		$content = file_get_contents("$path/$job");

		return array('', $content);
	}
	
	function fimo_get_upname($id, $job)
	{
		include('variables.php');
		$path = "$UPDIR_PATH/$id/$INPUT_FOLDER";
		
		if (!file_exists("$path/$job.filename"))
		{
			return array("Error: Input file for job $job not found !", '');
		}
		$content = file_get_contents("$path/$job.filename");

		return array('', $content);
	}
	
	function split_into_matrix($str)
	{
		$ret = array();

		foreach (explode("\n", $str) as $line)
		{
			$ret[] = explode("\t", $line);
		}

		return $ret;
	}
	
	function get_matches($keys, $data)
	{
		$matches = array();
		$n_patterns = sizeof($data);
		
		// First we initialise $matches
		foreach ($keys as $key)
		{
			$matches[$key] = array();
		}
		
		for ($i = 0; $i < $n_patterns; $i++)
		{
			$matches['>' . $data[$i][1]][] = $i;
		}
		
		return $matches;
	}
?>
