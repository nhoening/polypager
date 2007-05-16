#!/bin/bash

# upload all exported stuff to a server.
# This script needs to log you in, so as first argument pass the username, as second the servername
# as further arguments, provide a path to the target directory on the server 
# (as list like: a b c) from the root dir. 
# For example: bash upload2.bash user_name server_name dir1 dir2

# go to the polypager directory (contains export)
cd ../..

# remove PolyPager_Config.php (in order not to overwrite settings)
# comment this out for new installations!!
mv export/PolyPager_Config.php .

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
mv PolyPager_Config.php export
cd branches/maintenance
