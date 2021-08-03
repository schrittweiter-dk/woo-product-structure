import os
import csv
from csv import writer
import urllib.request
from urllib.parse import urlparse 

with open('input.csv') as f,open('output.csv', 'w', newline='') as write_obj:
	reader = csv.reader(f,delimiter = ',')
	header = next(reader)

	csv_writer = writer(write_obj)

	for row in reader:
		if row:
			dirname = "/".join((row[1].replace('/', '_').replace('"', '_').replace("'", '_').replace(":", '_'), row[3].replace('/', '_').replace('"', '_').replace("'", '_').replace(":", '_'), row[0].replace('/', '_').replace('"', '_').replace("'", '_').replace(":", '_')))
			if not os.path.exists(dirname):
				os.makedirs(dirname)

			files = row[2].split(",")
			newFiles = []
			for index, file in enumerate(files):
				if 'http' in file:	
					image = urllib.request
					path = urlparse(file).path
					ext = os.path.splitext(file)[1]

					keepcharacters = (' ','.','_')
					filenameA = "".join(c for c in row[3] if c.isalnum() or c in keepcharacters).rstrip()
					filenameB = "".join(c for c in row[0] if c.isalnum() or c in keepcharacters).rstrip()
					filename = filenameA + '_-_' + filenameB + '_' + str(index + 1) + ext
					
					image.urlretrieve(file, dirname + '/' + filename)

					sanitizeFilename = os.popen("php php/sanitize.php %s"%(filename)).read()
					newFiles.append(sanitizeFilename)

			row.append(",".join(newFiles))
			csv_writer.writerow(row)