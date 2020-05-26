#!/bin/bash

echo "##  initialize workspace variables"
source /opt/ros/melodic/setup.bash
cd ~/catkin_ws/
source devel/setup.bash

RUN_TIMEOUT=`grep "run_timeout=" /submission/submission.info | cut -d "=" -f 2`
TESTCASE_LAUNCH=`grep "testcase_launch=" /submission/submission.info | cut -d "=" -f 2`
echo "##  execute testcases (timeout $RUN_TIMEOUT seconds, launchfile: $TESTCASE_LAUNCH)"
roslaunch $TESTCASE_LAUNCH test.launch --log > /tmp/output/roslaunch.output 2> /tmp/output/roslaunch.error &
sleep 1
R_PID=`pgrep roslaunch`
for i in `seq 1 $RUN_TIMEOUT`
do
    kill -0 $R_PID || break
    echo -n "$i "
    sleep 1
done;
echo ""
echo "##  killing roslaunch";
kill $R_PID
sleep 5

echo "##  copy results"
cp -arv /tmp/output/* /output/.
cp -arv /tmp/recording.bag /output/.
# cp -arv /home/tester/.ros/log/latest/rosout.log /output/.
cp -arv /home/tester/.ros/log/latest/* /output/.

