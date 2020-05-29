<h3>How to use the automatic simulator execution system</h3>
This system is intended for automatic execution of your lab submissions in a defined environment.<br />
Optional testcases provide direct feedback if your solutions meets certain requirements.<br />
Detailed log output, rosbag and video files are provided for debugging purposes.<br />

<h4>User selection</h4>
On the left side you can select the users for which you want to upload a new submission file or inspect log files.<br />
Your personal user (your lastname) should be used for individual labs. Team users should be used for collaborative labs.<br />

<h4>Upload new submission</h4>
To upload a new submission archive you first need to select the user (see above).<br />
All submissions must be uploaded as <strong>zip</strong> archive. The selection of the appropriate determines the testcase and map.<br />
After uploading the file it will be added for batch processing. Please wait some minutes until results are available.

<h4>Batch processing</h4>
After uploading the file it is listed in the middle section indicating the upload date and time.<br />
A icon in the list indicates the status of the submission:<br />
<img src="./images/time.png" title="added for batch processing, please wait some minutes until results are available" alt="added for batch processing, please wait some minutes until results are available" /> Added for batch processing, please wait some minutes until execution is started.<br />
<img src="./images/rocket.png" title="execution currently running, please wait some minutes until results are available" alt="execution currently running, please wait some minutes until results are available" /> Execution currently running, please wait some minutes until results are available.<br />
<img src="./images/book.png" title="execution finished, for details see log files" alt="execution finished, for details see log files" /> Execution finished, for details see log files.<br />
<img src="./images/video.png" title="video file exists" alt="video file exists" /> This additional icon indicates that there is a video file available for this execution.<br />

<h4>Execution output (log files)</h4>
After the execution is finished the output log files and a video might be available.<br />
The list on the right side includes all relevant ros log files (*.log) and the following additional files.<br />
<img src="./images/packed.png" /> Your uploaded submission archive (upload.zip).<br />
<img src="./images/video.png" /> Recorded video (video.mp4).<br />
<img src="./images/log.png" /> Main log file of simulator execution (simulation-run.output contains stdout output).<br />
<img src="./images/log.png" /> Main log file of simulator execution (simulation-run.error contains stderr output).<br />
<img src="./images/packed.png" /> Compressed rosbag containing all topics (recording.bag.xz).<br />
<img src="./images/meta.png" /> Metadata of your submission (submission.info), containing map name, timeout settings, etc.<br />
<img src="./images/passed.png" /> Indicates that a logile reported "TESTCASE PASSED!".<br />
<img src="./images/failed.png" /> Indicates that a logile reported "TESTCASE FAILED!".<br />

<h4>Reproducibility and transparency</h4>
The relevant source files for this system are available in the following git repository: 
<a href="https://github.com/oddest-prime/arc-2020" target="_blank">https://github.com/oddest-prime/arc-2020</a><br />
The current git hash is shown at the bottom section of this page. For every execution the current git hash is logged (simulation-run.output).<br />

<h4>Contact</h4>
In case of any further questions regarding the automatic simulator execution system contact the lecturer team by email.
