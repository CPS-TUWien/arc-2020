#!/bin/bash

# initialize workspace variables
source /opt/ros/melodic/setup.bash
cd ~/catkin_ws/
source devel/setup.bash

# execute testcases
roslaunch testbench wall.launch > /tmp/output/roslaunch.output 2> /tmp/output/roslaunch.error &
for i in `seq 1 30`
do
    echo -n "r:$i "
    sleep 1
done;
R_PID=`pgrep roslaunch`
echo "killing roslaunch PID $R_PID";
kill $R_PID

sleep 2

# copy results
rm -rf /output/files/*
cp -arv /tmp/output/* /output/files/.
cp -arv /tmp/recording.bag /output/files/.
mkdir /output/files/roslog
cp -arv /home/tester/.ros/log/latest/ /output/files/roslog/.

