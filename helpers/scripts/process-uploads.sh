#!/bin/bash


lockfile -r 0 /tmp/simulator.lock || exit 1

cd /remotesim/repo
HASH=`git rev-parse HEAD`

cd /remotesim/uploads

for grp in `ls`
do
    for folder in `ls $grp`
    do
	MYLOG="$grp/$folder/simulation-run.log"
	if test -e $MYLOG
	then
	    echo "user: $grp - folder: $folder already processed."
	else
	    echo "user: $grp - folder: $folder start now:"
	    date > $MYLOG
	    echo "starting simulation run" >> $MYLOG
	    echo -n "git hash for simulator and scripts: $HASH" >> $MYLOG
	    echo "================================" >> $MYLOG
	    rm -rf /remotesim/submission/*
	    rm -rf /remotesim/output/*
	    cp $grp/$folder/upload.zip /remotesim/submission/.
	    docker run -ti -v /remotesim/repo:/repo:ro -v /remotesim/submission:/submission:rw -v /remotesim/output:/output:rw arc /repo/helpers/scripts/tester-exec.sh | tee -a $MYLOG
	    mv /remotesim/output/* $grp/$folder/.
	    echo "================================" >> $MYLOG
	    date >> $MYLOG
	    echo "simulation run completed." >> $MYLOG
	fi
    done
done

rm -f /tmp/simulator.lock
