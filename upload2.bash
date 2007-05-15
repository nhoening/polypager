#!/bin/bash

# upload all exported stuff to a server.
# This script needs to log you in, so ...
# as arguments, provide a path to the target directory on the server 
# (as list like: a b c) from the root dir. 


# make file descriptor (FD) 6 a copy of stdout (FD 1)
exec 6>&1
# open tmpfile for writing
exec 1>tmplftpfile

for a in $*; do
    echo "cd $a"
done
echo "mirror -R ..."

# close file
exec 1>&6
# close FD 6
exec 6>&-

#lftp -f tmplftpfile
#rm tmplftpfile
