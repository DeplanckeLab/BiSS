if ARGV.length < 1
	puts "Usage ruby convert_to_io.rb path/to/the/input path/to/the/output"
	exit 1
end

raw_content = File.open(ARGV[0], "r") { |f| f.readlines.map &:chomp }
new_content = raw_content.map { |line| line.gsub /[-.]/, "_" }
File.open(ARGV[1], "w") { |f| f.write new_content.join("\n") }
