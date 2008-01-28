#!/bin/bash

# copy a state of the trunk as a new version
# see also: http://svnbook.red-bean.com/en/1.1/re07.html 
#       -pass version

if [ "$1" == "" ]; then
	echo usage: ./dist.bash {version} or bash dist.bash {version}
else
	cd ../../
	svn copy trunk tags/$1
	cd branches/maintenance
fi

# upload to code.google.com with a script:
# http://code.google.com/support/bin/answer.py?answer=56630&topic=10456
python googlecode-upload.py -s $1 -l Featured -p polypager -u nhoening ../../dist/$1/PolyPager_$1.zip
# the skins are not as easy. sometimes they have not changed. and their fileiname can't be used twice in Google Code...
#python googlecode-upload.py -s $1 -l Featured -p polypager -u nhoening ../../dist/$1/polly.zip
#python googlecode-upload.py -s $1 -l Featured -p polypager -u nhoening ../../dist/$1/fscreen.zip
#python googlecode-upload.py -s $1 -l Featured -p polypager -u nhoening ../../dist/$1/picswap.zip


