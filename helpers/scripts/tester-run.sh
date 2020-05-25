#!/bin/bash

# initialize workspace variables
source /opt/ros/melodic/setup.bash
cd ~/catkin_ws/
source devel/setup.bash

rm -rf /tmp/video.mp4
killall Xvfb > /dev/null 2> /dev/null # kill orphaned process

# execute testcases
roslaunch testbench wall.launch > /tmp/output/roslaunch.output 2> /tmp/output/roslaunch.error &
for i in `seq 1 60`
do
    echo -n "r:$i "
    sleep 1
done;
R_PID=`pgrep roslaunch`
echo "killing roslaunch PID $R_PID";
kill $R_PID

# copy results


