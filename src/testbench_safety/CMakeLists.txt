cmake_minimum_required(VERSION 2.8.3)
project(testbench_safety)
set(CMAKE_CXX_STANDARD 11)
set(CMAKE_BUILD_TYPE Release)
set(CMAKE_CXX_FLAGS_RELEASE "-O3")
find_package(catkin REQUIRED COMPONENTS
  ackermann_msgs
  geometry_msgs
  nav_msgs
  roscpp
  rospy
  sensor_msgs
  std_msgs
  message_generation
)

catkin_package(CATKIN_DEPENDS message_runtime
)
include_directories(
  ${catkin_INCLUDE_DIRS}
)

add_executable(testbench_safety src/testbench.cpp)

target_link_libraries(testbench_safety
  ${catkin_LIBRARIES}
)

