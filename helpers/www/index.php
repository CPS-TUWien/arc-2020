<?php

define("PERM_FILE", "/remotesim/.htperms");
define("SUBMISSION_FOLDER", "/remotesim/uploads/");

$labs = array(	"safety" 		=> array(	"pkg_folder" => "safety_node",
							"pkg_name" => "safety_node",
							"testcase_launch" => "testbench_safety",
							"run_timeout" => "40",
							"video_timeout" => "40"),
		"wall_follow" 		=> array(	"pkg_folder" => "wall_follow",
							"pkg_name" => "wall_follow",
							"testcase_launch" => "testbench_wall_follow",
							"run_timeout" => "90",
							"video_timeout" => "100")
	    );

$special_files = array(	"video.mp4" => array("img" => "video", "info" => "recorded video"),
			"upload.zip" => array("img" => "packed", "info" => "your uploaded submission archive"),
			"simulation-run.error" => array("img" => "log", "info" => "main log file of simulator execution (stderr)"),
			"simulation-run.output" => array("img" => "log", "info" => "main log file of simulator execution (stdout)"),
			"submission.info" => array("img" => "meta", "info" => "metadata of your submission"),
		    );

function get_perms($user)
{
    $perms = array();

    if(($handle = fopen(PERM_FILE, "r")) !== FALSE)
    {
    while (($data = fgetcsv($handle, 1000, ":")) !== FALSE)
	{
	$num = count($data);
	if($data[0] === $user)
	    $perms = explode(",", $data[1]);
	}
    }
    fclose($handle);

    return $perms;
}

function get_submissions($user, $dir = "")
{
    $dirs = array();
    if(empty($user))
	return array();

    if ($handle = opendir(SUBMISSION_FOLDER."/".$user."/".$dir))
    {
	while (false !== ($entry = readdir($handle)))
	{
	    if($entry != "." && $entry != "..")
	    {
		$sz = filesize(SUBMISSION_FOLDER."/".$user."/".$dir."/".$entry);
		$mime = mime_content_type(SUBMISSION_FOLDER."/".$user."/".$dir."/".$entry);
		$dirs[$entry] = array("size" => $sz, "mime" => $mime, "lab" => $lab);
		if(file_exists(SUBMISSION_FOLDER."/".$user."/".$dir."/".$entry."/submission.info"))
		{
			$info = file_get_contents(SUBMISSION_FOLDER."/".$user."/".$dir."/".$entry."/submission.info");
			$tmp = explode("\n", $info);
			$lab = trim(str_replace("lab=", "", $tmp[0]));
			foreach($tmp as $line)
			{
				$t = explode("=", $line);
				$dirs[$entry][$t[0]] = $t[1];
			}
		}
	    }
	}
	closedir($handle);
    }

    ksort($dirs);
    return $dirs;
}

$user = empty($_SERVER["REMOTE_USER"]) ? "" : $_SERVER["REMOTE_USER"];
$perms = get_perms($user);
$g_user = empty($_GET["user"]) ? "" : $_GET["user"];
if(!in_array($g_user, $perms))
	$g_user = "";
$dirs = get_submissions($g_user);
$g_dir = empty($_GET["dir"]) ? "" : $_GET["dir"];
if(!in_array($g_dir, array_keys($dirs)))
	$g_dir = "";
$files = get_submissions($g_user, $g_dir);
$g_file = empty($_GET["file"]) ? "" : $_GET["file"];
if(!in_array($g_file, array_keys($files)))
	$g_file = "";
$g_lab = empty($_POST["lab"]) ? "" : $_POST["lab"];
if(!in_array($g_lab, array_keys($labs)))
	$g_lab = "";

if(!empty($_FILES) && !empty($g_user) && !empty($g_lab))
{
    $upload_time = time();
    $target_dir = SUBMISSION_FOLDER."/".$g_user."/".$upload_time;
    $target_file = $target_dir."/upload.zip";
    $target_info = $target_dir."/submission.info";
    @mkdir($target_dir, 0777, true);
    @chmod($target_dir, 0777);

    file_put_contents($target_info, "lab=".$g_lab."\n".
				    "upload_time=".$upload_time."\n".
				    "upload_time_readable=".date("Y-m-d H:i:s (T)", $upload_time)."\n".
				    "upload_user=".$user."\n".
				    "target_user=".$g_user."\n".
				    "pkg_name=".$labs[$g_lab]["pkg_name"]."\n".
				    "pkg_folder=".$labs[$g_lab]["pkg_folder"]."\n".
				    "testcase_launch=".$labs[$g_lab]["testcase_launch"]."\n".
				    "run_timeout=".$labs[$g_lab]["run_timeout"]."\n".
				    "video_timeout=".$labs[$g_lab]["video_timeout"]."\n".
				    ""
			);

    if (!move_uploaded_file($_FILES["file"]["tmp_name"], $target_file))
	die("Error: Could not move uploaded file.");
    else
	die('Your file was successfully uploaded and added for batch processing.<br />Please wait some minutes until results are available.<br /><a href="?user='.$g_user.'">continue</a>');
}

if(!empty($g_user) && !empty($g_dir) && !empty($g_file) && !empty($_GET["dl"]))
{
    $fp = fopen(SUBMISSION_FOLDER."/".$g_user."/".$g_dir."/".$g_file, 'rb');

    header('Content-Description: File Transfer');
    header('Content-Type: '.$files[$g_file]["mime"]);
    header('Content-Length: '.$files[$g_file]["size"]);
    header('Content-Disposition: attachment; filename="'.$g_user.'_'.$g_dir.'_'.$g_file.'"');
    header('Content-Transfer-Encoding: binary');
    fpassthru($fp);
    exit;
}

echo '<!DOCTYPE html>
<html>
<head>
  <title>ARC</title>
  <style>
    body {font-family: Arial;}
    table {border-collapse: collapse; border: none;}
    td,th {border: none; height: 28px;}
    th {background: #ccc;}
    tr:nth-child(even) {background: #F5F5F5;}
    tr:nth-child(odd) {background: #DDDDDD;}
    tr.selected {background: #FFCCCC;}
    img {width: 24px; height: 24px; padding-right: 4px; }
    h1 img {width: 128px; height: 41px; }
    h1 {color: #DD0000;}
  </style> 
  <link rel="icon" type="image/png" href="./images/race.png" sizes="128x128">
</head>
<body>';
echo '<h1><img src="./images/race_crop.png" alt="racing car" /> 191.119 Autonomous Racing Cars (VU 4,0) 2020S</h1>';
echo '<h3>automatic simulator execution system</h3>';

// --------------------

echo '<div style="float: left; padding: 3px;">';
echo '<table border="1">';
echo '<tr><th></th><th>select user</th></tr>';
foreach($perms as $u)
{
    if($g_user == $u)
        echo '<tr class="selected">';
    else
        echo '<tr>';
    echo '<td><img src="./images/race.png" alt="racing car" /></td>';
    echo '<td><a href="?user='.$u.'">'.$u.'</a></td>';
    echo '</tr>';
}
echo '</table>';
echo '</div>';

if(!empty($g_user))
{
    echo '<div style="float: left; padding: 3px;">';
    echo '<table border="1" style="border-collapse: none;">';
    echo '<tr><th></th><th>select submission</th><th>lab</lab></tr>';
    foreach($dirs as $d => $details)
    {
        if($g_dir == $d)
            echo '<tr class="selected">';
        else
            echo '<tr>';

        echo '<td>';
	$v_exists = file_exists(SUBMISSION_FOLDER."/".$g_user."/".$d."/video.mp4");
	$l_exists = file_exists(SUBMISSION_FOLDER."/".$g_user."/".$d."/simulation-run.output");
	$finished = FALSE;
	if($v_exists)
	    $finished = TRUE;
	else
	{
	    if($l_exists)
	    {
		$content = file_get_contents(SUBMISSION_FOLDER."/".$g_user."/".$d."/simulation-run.output");
		if(strstr($content, "simulation run completed."))
		    $finished = TRUE;
		elseif(strstr($content, "starting simulation run"))
		    $finished = "running";
		else
		    $finished = "other";
	    }
	    else
		$finished = "pending";
	}
	if($finished === TRUE)
	    echo '<img src="./images/book.png" title="execution finished, for details see log files" alt="execution finished, for details see log files" />';
	elseif($finished === "running")
	    echo '<img src="./images/rocket.png" title="execution currently running, please wait some minutes until results are available" alt="execution currently running, please wait some minutes until results are available" />';
	else
	    echo '<img src="./images/time.png" title="added for batch processing, please wait some minutes until results are available" alt="added for batch processing, please wait some minutes until results are available" />';
	if($v_exists)
	    echo '<img src="./images/video.png" title="video file exists" alt="video file exists" />';
        echo '</td>';

        echo '<td><a href="?user='.$g_user.'&dir='.$d.'">'.date("Y-m-d H:i:s (T)", intval($d)).'</a></td><td>'.$details["lab"].'</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<br />';
    echo '<form method="post" action="?user='.$g_user.'" enctype="multipart/form-data">';
    echo '<strong>upload new file</strong><br />';
    echo '<input type="file" name="file" /><br />';
    echo 'lab: <select name="lab">';
    foreach($labs as $l => $details)
	echo '<option>'.$l.'</option>';
    echo '</select><br />';
    echo 'submit: <input type="submit" value="upload file" />';
    echo '</form>';
    echo '</div>';
}

if(!empty($g_user) && !empty($g_dir))
{
    echo '<div style="float: left; padding: 3px;">';
    echo '<table border="1" style="border-collapse: none;">';
    echo '<tr><th> </th><th>select file</th><th>size (bytes)</th><th>mimetype</th><th>download</th></tr>';
    foreach($files as $f => $details)
    {
	if($details["size"] == 0) // do not list empty files
	    continue;
        if($g_file == $f)
            echo '<tr class="selected">';
        else
            echo '<tr>';

        echo '<td>';
	if(strstr($f, ".log"))
	{
	    $content = file_get_contents(SUBMISSION_FOLDER."/".$g_user."/".$g_dir."/".$f);
	    if(strstr($content, "Testbench. TESTCASE FAILED!"))
		echo '<img src="./images/failed.png" title="TESTCASE FAILED!" alt="TESTCASE FAILED!" />';
	    if(strstr($content, "Testbench. TESTCASE PASSED!"))
		echo '<img src="./images/passed.png" title="TESTCASE PASSED!" alt="TESTCASE PASSED!" />';
	}
        if(isset($special_files[$f]))
            echo '<img src="./images/'.$special_files[$f]["img"].'.png" title="'.$special_files[$f]["info"].'" alt="'.$special_files[$f]["info"].'" />';
        echo '</td>';

        echo '<td><a href="?user='.$g_user.'&dir='.$g_dir.'&file='.$f.'">'.$f.'</a></td><td>'.$details["size"].'</td><td>'.$details["mime"].'</td><td><a href="?user='.$g_user.'&dir='.$g_dir.'&file='.$f.'&dl=true">download</a></td>';
	echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
}

echo '<div style="clear: both;">';
echo '</div>';

if(!empty($g_user) && !empty($g_dir) && !empty($g_file))
{
    echo '<strong>file content of '.$g_file.'</strong><br />';
    echo 'size: '.$files[$g_file]["size"].' bytes<br />';
    echo 'mimetype: '.$files[$g_file]["mime"].'<br />';
    echo 'sha1: '.sha1_file(SUBMISSION_FOLDER."/".$g_user."/".$g_dir."/".$g_file).'<br />';
    echo '<pre>';
    if($files[$g_file]["mime"] != "text/plain" && $files[$g_file]["mime"] != "text/x-c")
	echo "file is not text/plain (use download link instead)";
    elseif($files[$g_file]["size"] >= 1024*50)
	echo "file larger than 50kiB (use download link instead)";
    else
	echo htmlentities(file_get_contents(SUBMISSION_FOLDER."/".$g_user."/".$g_dir."/".$g_file));
    echo '</pre>';
}



echo "<hr />logged in as user: ".$_SERVER["REMOTE_USER"]."; access for: ".implode(", ", $perms);
echo '</body></html>';

?>