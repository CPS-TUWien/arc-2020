#!/bin/bash

# initialize workspace variables
source /opt/ros/melodic/setup.bash
cd ~/catkin_ws/
source devel/setup.bash

# execute roslaunch
TESTCASE_LAUNCH=`grep "testcase_launch=" /submission/submission.info | cut -d "=" -f 2`
roslaunch ${TESTCASE_LAUNCH} play.launch > /tmp/output/roslaunch.output 2> /tmp/output/roslaunch.error

