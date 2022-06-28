# BiSS
Yeast one-hybrid screening for DNA-protein interactions

# TF motif database
The Transcription Factor database used is for *Drosophila melanogaster*, it consists of a file with [:page_facing_up: PWM motifs](https://github.com/DeplanckeLab/BiSS/blob/main/db/dmel_only.meme-io), and an [:page_facing_up: annotation file](https://github.com/DeplanckeLab/BiSS/blob/main/db/annotations.tsv).

This database was created by aggregating data from the [Fly Factor Survey](https://mccb.umassmed.edu/ffs/), [JASPAR](https://jaspar.genereg.net/search?q=&collection=CORE&tax_group=insects&tax_id=7227), [Transfac](http://gene-regulation.com/pub/databases.html#transfac), hdpi, [YeTFaSCo](http://yetfasco.ccbr.utoronto.ca/), consensus data from [SeleX](https://pubmed.ncbi.nlm.nih.gov/19231809/), data from [Elemento & Tavazoie](https://genomebiology.biomedcentral.com/articles/10.1186/gb-2005-6-2-r18), as well as data from [Stark lab](https://starklab.org/data/index.html).

# Website
This is the source code of our interactive website BiSS, which is available at [https://biss.epfl.ch/](https://biss.epfl.ch/)

# How do we get the transcription factors ?
We use [:computer: FIMO](https://meme.nbcr.net/meme/doc/fimo.html) to screen, and then annotate its output by adding a **binding site** column, those being found by querying our *Drosophila melanogaster* TF motif database (see above)

# What sequence type can be submitted ?
Sequences should be in FASTA format. You can input one sequence without header, and it will be named **user input**, but it has still to be in FASTA format.
Also, to prevent misuse (see below) of this website, and overload of the server, length is limited to 10000 base pairs per sequence.
