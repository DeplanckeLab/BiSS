#! /bin/bash

ID_="$1"
JOB_="$2"
PATH_="$3"
INPUT_="$4"
OUTPUT_="$5"
DB_="$6"
UPDIR_RIGHTS_="$7"
UPFILES_RIGHTS_="$8"
FIMO_USELESS_="$9"
LOG_="${10}"
FIMO_OUTPUT_="${11}"
TEMP_FIMO_ANNOTATED_="${12}"
GENE_ANNOTATIONS_DB_="${13}"
OPTS_="${14}"

if [ -z "$ID_" ]
then
	echo 'Error: ID not given !'
	exit 1
fi
if [ -z "$JOB_" ]
then
	echo 'Error: JOB not given !'
	exit 1
fi
if [ -z "$PATH_" ]
then
	echo 'Error: PATH not given !'
	exit 1
fi
if [ -z "$INPUT_" ]
then
	echo 'Error: INPUT not given !'
	exit 1
fi
if [ -z "$OUTPUT_" ]
then
	echo 'Error: OUTPUT not given !'
	exit 1
fi
if [ -z "$DB_" ]
then
	echo 'Error: DB not given !'
	exit 1
fi
if [ -z "$UPDIR_RIGHTS_" ]
then
	echo 'Error: UPDIR_RIGHTS not given !'
	exit 1
fi
if [ -z "$UPFILES_RIGHTS_" ]
then
	echo 'Error: UPFILES_RIGHTS not given !'
	exit 1
fi

echo 'Running' > "$OUTPUT_.status"
chmod $UPFILES_RIGHTS_ $OUTPUT_.status
# First we perform fimo
/opt/meme/bin/fimo $OPTS_ --o $OUTPUT_ $DB_ $INPUT_

# Then we set the permissions right
chmod $UPDIR_RIGHTS_ $OUTPUT_
FIMO_FILES=`ls $OUTPUT_`
for file in $FIMO_FILES
do
	TO_DELETE=`echo $FIMO_USELESS_ | grep $file`
	if [ -n "$TO_DELETE" ]
	then
		rm "$OUTPUT_/$file"
	else
		chmod $UPFILES_RIGHTS_ "$OUTPUT_/$file"
	fi
done
chmod $UPFILES_RIGHTS_ $LOG_

# Finally, we annotate the fimo output
python annotate.py "$OUTPUT_/$FIMO_OUTPUT_" "$GENE_ANNOTATIONS_DB_" "$OUTPUT_/$TEMP_FIMO_ANNOTATED_"
chmod $UPFILES_RIGHTS_ "$OUTPUT_/$TEMP_FIMO_ANNOTATED_"
mv "$OUTPUT_/$TEMP_FIMO_ANNOTATED_" "$OUTPUT_/$FIMO_OUTPUT_"

# Indicating that the job is finished
# It may happen that the job was deleted
# before it got finished, so we test first.
if [ -f "$OUTPUT_.status" ]
then
	echo 'Finished' > "$OUTPUT_.status"
fi
