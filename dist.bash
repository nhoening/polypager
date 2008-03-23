#!/bin/bash

# makes a ZIP and a TAR.GZ out of the actual state and a Zip for each skin, 
# needs a version number
# attempts to create a new directory in the dist directory with all the archives

if [ "$1" == "" ]; then
	echo usage: ./dist.bash {version} or bash dist.bash {version}
else
    # export from trunk
    cd ../../trunk
    rm -r ../export
    svn export . ../export
    cd ../export
    mkdir ../dist/$1
    
    # first extract skins
    cd style/skins
    zip -r ../../../dist/$1/picswap.zip picswap
    zip -r ../../../dist/$1/polly.zip polly
    zip -r ../../../dist/$1/fscreen.zip fscreen
    # keep only default
    rm -rf picswap
    rm -rf fscreen
    cd ../..
    
    # now archive the rest
    zip -r ../dist/$1/PolyPager_$1.zip *
    tar -cf ../dist/$1/PolyPager_$1.tar *
    gzip -S .gz --best ../dist/$1/PolyPager_$1.tar 
    cd ../branches/maintenance
fi
