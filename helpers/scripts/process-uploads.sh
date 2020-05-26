#!/bin/bash

LOCKDIR="/tmp/simulator.lock"
PIDFILE="${LOCKDIR}/PID"

# start locking attempt // http://wiki.bash-hackers.org/howto/mutex
if mkdir "${LOCKDIR}" &>/dev/null; then
    echo "$$" >"${PIDFILE}" 
    echo "success, locked" 
else
    # lock failed, check if the other PID is alive
    OTHERPID="$(cat "${PIDFILE}")"
    if ! kill -0 $OTHERPID &>/dev/null; then
        echo "removing stale lock of nonexistant PID ${OTHERPID}"
        rm -rf "${LOCKDIR}"
        exit 10;
    else
        echo "lock failed, PID ${OTHERPID} is active"
        exit 11;
    fi
fi



cd /remotesim/repo
HASH=`git rev-parse HEAD`

cd /remotesim/uploads

for grp in `ls`
do
    for folder in `ls $grp`
    do
	MYLOG="$grp/$folder/simulation-run.output"
	MYERR="$grp/$folder/simulation-run.error"
	if test -e $MYLOG
	then
	    echo "user: $grp - folder: $folder already processed."
	else
	    echo "user: $grp - folder: $folder start now:"
	    date > $MYLOG
	    echo "starting simulation run" >> $MYLOG
	    echo "git hash for simulator and scripts: $HASH" >> $MYLOG
	    echo "================================" >> $MYLOG
	    rm -rf /remotesim/submission/*
	    rm -rf /remotesim/output/*
	    cp $grp/$folder/upload.zip /remotesim/submission/.
	    cp $grp/$folder/submission.info /remotesim/submission/.
	    docker run --rm -v /remotesim/repo:/repo:ro -v /remotesim/submission:/submission:rw -v /remotesim/output:/output:rw arc /repo/helpers/scripts/tester-exec.sh > >(tee -a $MYLOG) 2> >(tee -a $MYERR >&2)
	    # manual interactive start:
	    # docker run -ti -v /remotesim/repo:/repo:ro -v /remotesim/submission:/submission:rw -v /remotesim/output:/output:rw arc bash
	    mv /remotesim/output/* $grp/$folder/.
	    echo "================================" >> $MYLOG
	    date >> $MYLOG
	    echo "simulation run completed." >> $MYLOG
	fi
    done
done


rm -rf "${LOCKDIR}"
