import os
import csv
import urllib.request
from urllib.parse import urlparse 

with open('input.csv') as f:
	reader = csv.reader(f,delimiter = ',')
	header = next(reader)
	for row in reader:
		if row:
			dirname = "/".join((row[1].replace('/', '_').replace('"', '_').replace("'", '_').replace(":", '_'), row[3].replace('/', '_').replace('"', '_').replace("'", '_').replace(":", '_'), row[0].replace('/', '_').replace('"', '_').replace("'", '_').replace(":", '_')))
			if not os.path.exists(dirname):
				os.makedirs(dirname)

			files = row[2].split(",")
			for index, file in enumerate(files):
				if 'http' in file:	
					image = urllib.request
					path = urlparse(file).path
					ext = os.path.splitext(file)[1]

					keepcharacters = (' ','.','_')
					filenameA = "".join(c for c in row[3] if c.isalnum() or c in keepcharacters).rstrip()
					filenameB = "".join(c for c in row[0] if c.isalnum() or c in keepcharacters).rstrip()

					image.urlretrieve(file, dirname + '/' + filenameA + '_-_' + filenameB + '_' + str(index + 1) + ext)