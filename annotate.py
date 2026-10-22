import os, sys

if len(sys.argv) != 4:
	print("Usage: python3 annotate.py path/to/fimo/file path/to/gene/file path/to/new/fimo/file")
	sys.exit(1)

if not os.path.isfile(sys.argv[1]):
	print("Error: Couldn't open file {0} !".format(sys.argv[1]))
	sys.exit(1)
if not os.path.isfile(sys.argv[2]):
	print("Error: Couldn't open file {0} !".format(sys.argv[2]))
	sys.exit(1)
if os.path.isfile(sys.argv[3]):
	print("Error: File {0} already exists !".format(sys.argv[3]))
	sys.exit(1)

def not_empty(el):
	return (el != "")

def not_comment(el):
	return not el.startswith("#")

with open(sys.argv[1], "r", encoding="utf-8") as file:
	fimo_input = list(filter(not_comment, filter(not_empty, file.read().split("\n"))))
with open(sys.argv[2], "r", encoding="utf-8") as file:
	gene_input = list(filter(not_empty, file.read().split("\n")))

# The website reads the FIMO 4 text format ("fimo.txt"), while FIMO 5 writes
# "fimo.tsv", which has an additional "motif_alt_id" column (2nd), a header
# without '#' and comment lines at the end (removed above).
if not fimo_input or fimo_input[0].split("\t")[:2] != ["motif_id", "motif_alt_id"]:
	print("Error: {0} is not a FIMO tsv file !".format(sys.argv[1]))
	sys.exit(1)
header = ["#pattern name", "sequence name", "start", "stop", "strand", "score", "p-value", "q-value", "matched sequence"]
fimo_input = ["\t".join(header)] + ["\t".join(line.split("\t")[:1] + line.split("\t")[2:]) for line in fimo_input[1:]]

# Motif id => gene name (the first annotation of a motif wins)
genes = {}
for gene in gene_input[1:]:
	temp = gene.split("\t")
	if len(temp) >= 3:
		genes.setdefault(temp[0], temp[2])

# Add tags to header ("motif description", "gene name")
tags = gene_input[0].replace("_", " ").split("\t")
#fimo_input[0] += "\t" + tags[1] + "\t" + tags[2]
# Version with only "gene name" as additional tag
fimo_input[0] += "\t" + tags[2]

for i in range(1, len(fimo_input)):
	tag = fimo_input[i].split("\t")[0]
	# Motifs without annotation are shown with their own name
	fimo_input[i] += "\t" + genes.get(tag, tag)

output = "\n".join(fimo_input)
with open(sys.argv[3], "w", encoding="utf-8") as file:
	file.write(output)
