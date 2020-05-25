#!/bin/bash

# initialize workspace variables
source /opt/ros/melodic/setup.bash
cd ~/catkin_ws/
source devel/setup.bash

# execute roslaunch
roslaunch testbench wall-play.launch > /tmp/output/roslaunch.output 2> /tmp/output/roslaunch.error

