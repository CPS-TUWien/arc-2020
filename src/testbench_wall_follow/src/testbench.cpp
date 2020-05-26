#include <ros/ros.h>
#include <tf2_geometry_msgs/tf2_geometry_msgs.h>
#include <nav_msgs/Odometry.h>
#include <sensor_msgs/LaserScan.h>
#include <std_msgs/String.h>
#include <std_msgs/Bool.h>
#include <geometry_msgs/PoseWithCovarianceStamped.h>
#include <geometry_msgs/PoseStamped.h>

#define N_RANGES 1080
#define FINISH_LINE_X 0.5
#define FINISH_LINE_WIDTH 1

class Testbench {
// The class that handles emergency braking
public:
    ros::NodeHandle n;
    ros::Publisher pose_pub;
    ros::Publisher key_pub;
    ros::Subscriber key_sub;
    ros::Subscriber pose_sub;
    ros::Subscriber collision_sub;
    ros::Timer test_timer;
    ros::Timer end_timer;

    float pose_x;
    float pose_y;
    float pose_yaw;
    float speed;
    std::string testcase_name;

    bool exit_node;
    bool brake_enabled;

    int laps;
    ros::Time last_lap;

public:
    Testbench() {
        n = ros::NodeHandle();

	brake_enabled = false;
	laps = -1;

        n.getParam("/test/exit_node", exit_node);
        n.getParam("/test/testcase_name", testcase_name);
	ROS_INFO("Testbench. Testcase: %s", testcase_name.c_str());

	key_sub = n.subscribe("key", 1, &Testbench::key_callback, this);
	pose_sub = n.subscribe("gt_pose", 1, &Testbench::pose_callback, this);
	collision_sub = n.subscribe("collision", 1, &Testbench::collision_callback, this);

	pose_pub = n.advertise<geometry_msgs::PoseWithCovarianceStamped>("initialpose", 1);
	key_pub = n.advertise<std_msgs::String>("key", 10);

	test_timer = n.createTimer(ros::Duration(5.0), &Testbench::timer_callback, this, true);
	end_timer = n.createTimer(ros::Duration(200.0), &Testbench::end_callback, this, true);
    }

    void pose_callback(const geometry_msgs::PoseStamped::ConstPtr &pose_msg) {
	if(pose_x < FINISH_LINE_X && pose_msg->pose.position.x > FINISH_LINE_X)
	    if(0-FINISH_LINE_WIDTH < pose_msg->pose.position.y && pose_msg->pose.position.y < FINISH_LINE_WIDTH) // finish line passed
		line_passed();

	pose_x = pose_msg->pose.position.x;
	pose_y = pose_msg->pose.position.y;
    }

    void collision_callback(const std_msgs::Bool::ConstPtr &collision_msg) {
	ROS_INFO("Testbench. Collision @ x=%f, y=%f", pose_x, pose_y);
	ROS_INFO("Testbench. TESTCASE FAILED!");
	if(exit_node)
		ros::shutdown();
    }

    void key_callback(const std_msgs::String::ConstPtr &key_msg) {
	if(key_msg->data == "t")
	{
		ROS_INFO("Testbench. Key pressed: %s", key_msg->data.c_str());
		start_test();
	}
    }

    void timer_callback(const ros::TimerEvent &timer_event)
    {
	ROS_INFO("Testbench. Starting test.");
	start_test();
    }

    void end_callback(const ros::TimerEvent &timer_event)
    {
	ROS_INFO("Testbench. Timeout reached, stop testcase.");

	if(exit_node)
		ros::shutdown();
    }

    void line_passed()
    {
	ROS_INFO("Testbench. Finish line passed.");
	laps ++;
	ROS_INFO("Testbench. Laps: %d", laps);

	if(laps == 1)
		ROS_INFO("Testbench. TESTCASE PASSED!");

	ros::Time current_lap = ros::Time::now();
	ros::Duration dur = current_lap - last_lap;
	if(laps != 0)
		ROS_INFO("Testbench. Lap duration time: %f sec.", dur.toSec());

	last_lap = current_lap;

	if(exit_node && laps >= 1)
		ros::shutdown();
    }

    void start_test()
    {
	ROS_INFO("Testbench. Enable wall-follow driving contoller.");

	std::string str;

	ros::Duration(0.2).sleep();
	std_msgs::String n_msg;
	str = "n";
	n_msg.data = str.c_str();
	key_pub.publish(n_msg); // enable wall-follow driving contoller
    }
};
int main(int argc, char ** argv) {
	ros::init(argc, argv, "testbench_name");
	Testbench tb;
	ros::spin();
	return 0;
}
