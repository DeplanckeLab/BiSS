<?php
	// Location of the website's code
	$BASE_ADDRESS = __DIR__;
	
	$N_RANDOM_CHARS = 20;
	$VALID_CHAR_LIST = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	$MAX_FASTA_LENGTH = 10000;
	
	// User data (one folder per session), deleted after $RETENTION_DAYS days.
	// In Docker, these are set by the image (see Dockerfile).
	$UPDIR_PATH = getenv('BISS_JOBS_DIR') ?: "$BASE_ADDRESS/tmp";
	$RETENTION_DAYS = (int) (getenv('BISS_RETENTION_DAYS') ?: 7);
	$INPUT_FOLDER = 'input';
	$FIMO_FOLDER = 'output.fimo';
	$LOG_FOLDER = 'log';
	$UPDIR_RIGHTS = 0770;
	$UPFILES_RIGHTS = 0660;

	$DB_PATH = "$BASE_ADDRESS/db";
	$DEFAULT_DB = 'dmel_only.meme-io';
	$GENE_ANNOTATIONS_DB = 'annotations.tsv';

	// Files written by FIMO (MEME Suite 5) that we don't use
	$FIMO_USELESS = array('best_site.narrowPeak', 'cisml.xml', 'fimo.gff', 'fimo.html', 'fimo.xml');
	// FIMO's own results, converted by annotate.py into $FIMO_TXT
	$FIMO_TSV = 'fimo.tsv';
	$FIMO_TXT = 'fimo.txt';
	$TEMP_FIMO_ANNOTATED = 'fimo.annotated.txt';
?>
