#!/bin/bash
FILES=/var/www/html/static/work/*.js
shopt -s nullglob
for f in $FILES
do
  /usr/local/bin/phantomjs "$f"
  echo "Processing $f file..."
  # cat "$f"
  
done
