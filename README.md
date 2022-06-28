# BiSS
Yeast one-hybrid screening for DNA-protein interactions

# Website
An interactive website is available at <a href="[http://example.com/](https://biss.epfl.ch/)" target="_blank">[example](https://biss.epfl.ch/)</a>

# How do we get the transcription factors ?
We first use FIMO to screen, and then modify its output by adding a binding site column, those being found by querying a database we created.

# What sequence type can I submit ?
Sequences should be in FASTA format. You can input one sequence without header, and it will be named user input, but it has still to be in FASTA format.
Also, to prevent misuse (see below) of this website, and overload of the server, length is limited to 10000 base pairs per sequence.
