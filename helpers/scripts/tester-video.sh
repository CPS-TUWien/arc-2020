#!/bin/bash

echo "##  initialize workspace variables"
source /opt/ros/melodic/setup.bash
cd ~/catkin_ws/
source devel/setup.bash

rm -rf /tmp/video.mp4
killall Xvfb > /dev/null 2> /dev/null # kill orphaned process

RUN_TIMEOUT=50
DISP_NUM=46
echo "##  run rosbag with rviz and record video (timeout $RUN_TIMEOUT seconds)"
xvfb-run --listen-tcp --server-num $DISP_NUM --auth-file /tmp/xvfb.auth -s "-ac -screen 0 1920x1080x24" /repo/helpers/scripts/tester-launch.sh &
tmux new-session -d -s VideoRecording$DISP_NUM "ffmpeg -framerate 15 -f x11grab -video_size 1920x1080 -i :$DISP_NUM -codec:v mpeg2video -b:v 6000k /tmp/video.mp4" &
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
sleep 2

tmux send-keys -t VideoRecording$DISP_NUM q
F_PID=`pgrep ffmpeg`
for i in `seq 1 10`
do
    kill -0 $F_PID 2>/dev/null || break;
    echo -n ". "
    sleep 1
done;
echo "##  killing ffmpeg";
kill $F_PID 2>/dev/null
sleep 2

echo "##  copy video"
cp -arv /tmp/video.mp4 /output/.

