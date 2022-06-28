content = IO.read "Supplemental_Dataset_1.txt"

profiles_raw = content.split(">")[1..-1]
profiles = profiles_raw.map do |raw|
	temp = raw.split "\n"
	name = temp[0].split("\t")[4].gsub ".", "_"
	raw_probabilities = temp[1..-1].map { |l| l.split "\t" }
	probabilities = raw_probabilities.transpose.map do |pro|
		pro.join "\t"
	end
	[name] + probabilities
end

output = profiles.inject "" do |r, s|
	temp = "MOTIF #{s[0]}\nletter-probability matrix: alength= 4\n"
#	for l in s[1..-1]
#		temp += l + "\n"
#	end
	temp += s[1] + "\n"
	temp += "0.25\t0.25\t0.25\t0.25\n" * 2
	temp += s[2] + "\n"
	temp += s[3] + "\n"
	temp += "0.25\t0.25\t0.25\t0.25\n" * 2
	temp += s[4] + "\n"
	temp += "\n"
	r + temp
end

IO.write "Supplemental_Dataset_1.meme-io", output
