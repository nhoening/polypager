#!/bin/bash

# upload all exported stuff to a server.
if [ "$1" == ""  ]; then
    echo 'This script needs to log you in, so as first argument pass the username, as second the servername'
    echo 'as further arguments, provide a path to the target directory on the server '
    echo '(as list like: a b c) from the root dir.' 
    echo 'For example: bash upload2.bash user_name server_name dir1 dir2'
    exit
fi

# go to the polypager directory (contains export)
cd ../..

# make file descriptor (FD) 6 a copy of stdout (FD 1)
exec 6>&1
# open tmpfile for writing
exec 1>tmplftpfile

echo "open $1@$2"
for a in $*; do
    if [ "$1" != "$a" ]; then
        if [ "$2" != "$a" ]; then
            echo "cd $a"
        fi
    fi
done
echo "mirror -R export ."

# close file
exec 1>&6
# close FD 6
exec 6>&-

lftp -f tmplftpfile $1

# clean up
rm tmplftpfile
cd branches/maintenance
