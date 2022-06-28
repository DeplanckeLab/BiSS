<?php
	$LAB_ADDRESS = 'biss.epfl.ch';
	$BASE_ADDRESS = '/data/www/sites/biss';
	
	$N_RANDOM_CHARS = 20;
	$VALID_CHAR_LIST = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	$MAX_FASTA_LENGTH = 10000;
	
	$UPDIR_PATH = "$BASE_ADDRESS/tmp";
	$INPUT_FOLDER = 'input';
	$FIMO_FOLDER = 'output.fimo';
	$LOG_FOLDER = 'log';
	$UPDIR_RIGHTS = 0770;
	$UPFILES_RIGHTS = 0660;

	$DB_PATH = "$BASE_ADDRESS/db";
	$DEFAULT_DB = 'dmel_only.meme-io';
	$GENE_ANNOTATIONS_DB = 'annotations.tsv';

	$FIMO_USELESS = array('cisml.css', 'cisml.xml', 'fimo.gff', 'fimo-to-html.xsl', 'fimo.xml', 'fimo.html');
	$FIMO_TXT = 'fimo.txt';
	$TEMP_FIMO_ANNOTATED = 'fimo.annotated.txt';
?>
