#!/bin/bash

# initialize workspace variables
source /opt/ros/melodic/setup.bash
cd ~/catkin_ws/
source devel/setup.bash

rm -rf /tmp/video.mp4
killall Xvfb > /dev/null 2> /dev/null # kill orphaned process

# execute testcases
DISP_NUM=46
xvfb-run --listen-tcp --server-num $DISP_NUM --auth-file /tmp/xvfb.auth -s "-ac -screen 0 1920x1080x24" /repo/helpers/scripts/tester-launch.sh &
tmux new-session -d -s VideoRecording$DISP_NUM "ffmpeg -framerate 25 -f x11grab -video_size 1920x1080 -i :$DISP_NUM -codec:v mpeg2video -b:v 6000k /tmp/video.mp4" &
for i in `seq 1 60`
do
    echo -n "r:$i "
    sleep 1
done;
R_PID=`pgrep roslaunch`
echo "killing roslaunch PID $R_PID";
kill $R_PID

tmux send-keys -t VideoRecording$DISP_NUM q
for i in `seq 1 10`
do
    echo -n "f:$i "
    sleep 1
done;
F_PID=`pgrep ffmpeg`
echo "killing ffmpeg PID $F_PID";
kill $F_PID

sleep 2

# copy results
cp /tmp/video.mp4 /output/files/.

