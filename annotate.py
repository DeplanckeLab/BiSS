import os, sys

if len(sys.argv) != 4:
	print("Usage: python annotate.py path/to/fimo/file path/to/gene/file path/to/new/fimo/file")
	exit(1)

if not os.path.isfile(sys.argv[1]):
	print("Error: Couldn't open file {0} !".format(sys.argv[1]))
	exit(1)
if not os.path.isfile(sys.argv[2]):
	print("Error: Couldn't open file {1} !".format(sys.argv[2]))
	exit(1)
if os.path.isfile(sys.argv[3]):
	print("Error: File {2} already exists !".format(sys.argv[3]))
	exit(1)

def not_empty(el):
	return (el != "")

with open(sys.argv[1], "r") as file:
	fimo_input = filter(not_empty, file.read().split("\n"))
with open(sys.argv[2], "r") as file:
	gene_input = filter(not_empty, file.read().split("\n"))

# Add tags to header ("motif description", "gene name")
tags = gene_input[0].replace("_", " ").split("\t")
#fimo_input[0] += "\t" + tags[1] + "\t" + tags[2]
# Version with only "gene name" as additional tag
fimo_input[0] += "\t" + tags[2]

for i in range(1, len(fimo_input)):
	tag = fimo_input[i].split("\t")[0]
	for gene in gene_input[1:-1]:
		temp = gene.split("\t")
		if tag == temp[0]:
			#fimo_input[i] += "\t" + temp[1] + "\t" + temp[2]
			# Version with only "gene name"
			fimo_input[i] += "\t" + temp[2]
			break

output = "\n".join(fimo_input)
with open(sys.argv[3], "w") as file:
	file.write(output)
