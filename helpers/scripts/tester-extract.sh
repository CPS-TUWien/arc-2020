#!/bin/bash

echo "##  extract student submission"
cd /submission/
if test -e upload.zip
then
    echo "File upload.zip exists."
else
    echo "File upload.zip missing."
    exit 1;
fi;

if file upload.zip | grep -q "Zip archive data"
then
    echo "File upload.zip is zip archive."
else
    echo "File upload.zip is no zip archive."
    exit 2;
fi;

unzip upload.zip
chmod 0777 * -R 2> /dev/null

if test -e package.xml
then
    echo "File package.xml exists."
else
    echo "File package.xml missing in your zip archive."
    exit 3;
fi;

if test -e CMakeLists.txt
then
    echo "File CMakeLists.txt exists."
else
    echo "File CMakeLists.txt missing in your zip archive."
    exit 4;
fi;

if test -d "src" -a -d "scripts"
then
    echo "Both directories \"src\" and \"scripts\" contained in archive. You should only include one of them."
    exit 5;
elif test -d "src"
then
    echo "Directory \"src\" exists in archive."
elif test -d "scripts"
then
    echo "Directory \"scripts\" exists in archive."
else
    echo "file CMakeLists.txt missing in your zip archive."
    exit 6;
fi;


echo "##  package.xml information"
grep "<maintainer" package.xml | grep -v "<!--"
grep "<author" package.xml | grep -v "<!--"

rm -f upload.zip

