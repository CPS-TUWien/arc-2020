#!/bin/bash

echo "##  initialize workspace variables"
source /opt/ros/melodic/setup.bash
cd ~/catkin_ws/
source devel/setup.bash

RUN_TIMEOUT=50
echo "##  execute testcases (timeout $RUN_TIMEOUT seconds)"
roslaunch testbench wall.launch > /tmp/output/roslaunch.output 2> /tmp/output/roslaunch.error &
sleep 1
R_PID=`pgrep roslaunch`
for i in `seq 1 $RUN_TIMEOUT`
do
    kill -0 $R_PID 2>/dev/null || break;
    echo -n "$i "
    sleep 1
done;
echo "##  killing roslaunch";
kill $R_PID
sleep 5

echo "##  copy results"
rm -rf /output/files/*
cp -arv /tmp/output/* /output/.
cp -arv /tmp/recording.bag /output/.
#mkdir /output/roslog
#cp -arv /home/tester/.ros/log/latest/ /output/roslog/.

