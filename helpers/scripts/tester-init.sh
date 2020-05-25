#!/bin/bash

echo "##  cleanup workspace"
rm -rf ~/catkin_ws
rm -rf /tmp/output
mkdir -p /tmp/output

echo "##  initialize workspace"
source /opt/ros/melodic/setup.bash
mkdir -p ~/catkin_ws/src
cd ~/catkin_ws/
echo "    (catkin_make output in files build-workspace.*)"
catkin_make > /tmp/output/build-workspace.output 2> /tmp/output/build-workspace.error
source devel/setup.bash

echo "##  copy templates"
cd ~/catkin_ws/src/
cp -ar /repo/src/* .

echo "##  build ros template packages"
cd ~/catkin_ws/
echo "    (catkin_make output in files build-templates.*)"
catkin_make > /tmp/output/build-templates.output 2> /tmp/output/build-templates.error

echo "##  copy submission files"
cd ~/catkin_ws/src/
rm -rf wall_follow
mkdir wall_follow
cd wall_follow
cp -arv /submission/* .

echo "##  build ros packages"
cd ~/catkin_ws/
echo "    (catkin_make output in files build-package.*)"
catkin_make > /tmp/output/build-package.output 2> /tmp/output/build-package.error


