#!/bin/bash

echo "##  extract student submission"
cd /submission/
if test -e upload.zip
then
    echo "file upload.zip exists"
else
    echo "file upload.zip missing."
    exit 1;
fi;

if file upload.zip | grep -q "Zip archive data"
then
    echo "file upload.zip is zip archive"
else
    echo "file upload.zip is no zip archive"
    exit 2;
fi;

unzip upload.zip

if test -e package.xml
then
    echo "file package.xml exists"
else
    echo "file package.xml missing in your zip archive."
    exit 3;
fi;

if test -e CMakeLists.txt
then
    echo "file CMakeLists.txt exists"
else
    echo "file CMakeLists.txt missing in your zip archive."
    exit 4;
fi;

echo "##  package.xml information"
grep "<maintainer" package.xml | grep -v "<!--"
grep "<author" package.xml | grep -v "<!--"

rm -f upload.zip




