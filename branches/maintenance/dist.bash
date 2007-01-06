#!/bin/bash

# makes a ZIP and a TAR.GZ out of the actual state, needs a version number

if [ "$1" == "" ]; then
	echo usage: ./dist.bash {version} or bash dist.bash {version}
else
	#[in trunk]
	cd ../../trunk
	rm -r ../export
	svn export . ../export
	cd ../export
	zip -r ../PolyPager_$1.zip *
	tar -cf ../PolyPager_$1.tar *
	gzip -S .gz --best ../PolyPager_$1.tar 
	cd ../branches/maintenance
fi
