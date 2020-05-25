FROM ubuntu:bionic
LABEL description="Base build image for ARC testbench"

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get -y install mc git gnupg
RUN echo "deb http://packages.ros.org/ros/ubuntu bionic main" > /etc/apt/sources.list.d/ros-latest.list \
    && apt-key adv --keyserver 'hkp://keyserver.ubuntu.com:80' --recv-key C1CF6E31E6BADE8868B172B4F42ED6FBAB17C654 \
    && apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get -y install ros-melodic-desktop-full ros-melodic-tf2-geometry-msgs ros-melodic-ackermann-msgs ros-melodic-joy ros-melodic-map-server
RUN mkdir -p /home/tester \
    && useradd --home /home/tester tester \
    && chown tester:tester /home/tester
RUN echo "tester:tester" | chpasswd \
    && adduser tester sudo
RUN DEBIAN_FRONTEND=noninteractive apt-get -y install xvfb tmux ffmpeg sudo x11-apps psmisc

USER tester
CMD /home/tester

