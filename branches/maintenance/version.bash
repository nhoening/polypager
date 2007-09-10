#!/bin/bash

# copy a state of the trunk as a new version
# see also: http://svnbook.red-bean.com/en/1.1/re07.html 
#       -pass version
#       -verify

if [ "$1" == "" ]; then
	echo usage: ./dist.bash {version} or bash dist.bash {version}
else
	cd ../../
	svn copy trunk tags/$1
	cd branches/maintenance
fi
