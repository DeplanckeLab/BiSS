# BiSS
Yeast one-hybrid screening for DNA-protein interactions

# Website
This is the source code of our interactive website BiSS, which is available at [https://biss.epfl.ch/](https://biss.epfl.ch/)

# How do we get the transcription factors ?
We first use FIMO to screen, and then annotate its output by adding a binding site column, those being found by querying a database we created.

# What sequence type can be submitted ?
Sequences should be in FASTA format. You can input one sequence without header, and it will be named user input, but it has still to be in FASTA format.
Also, to prevent misuse (see below) of this website, and overload of the server, length is limited to 10000 base pairs per sequence.
