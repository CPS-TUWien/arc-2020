<h1>ARC simulator</h1>
<?php

define("PERM_FILE", "/remotesim/.htperms");
define("SUBMISSION_FOLDER", "/remotesim/uploads/");

$labs = array(	"safety" 		=> array(	"pkg_folder" => "safety_node",
							"pkg_name" => "safety_node",
							"testcase_launch" => "testbench_safety",
							"run_timeout" => "30",
							"video_timeout" => "40"),
		"wall_follow" 		=> array(	"pkg_folder" => "wall_follow",
							"pkg_name" => "wall_follow",
							"testcase_launch" => "testbench_wall_follow",
							"run_timeout" => "90",
							"video_timeout" => "100")
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

// --------------------

echo '<div style="float: left; padding: 3px;">';
echo '<table border="1" style="border-collapse: none;">';
echo '<tr><th>select user</th></tr>';
foreach($perms as $u)
    if($g_user == $u)
        echo '<tr style="background: #ddd"><td><a href="?user='.$u.'"><strong>'.$u.'</strong></a></td></tr>';
    else
        echo '<tr><td><a href="?user='.$u.'">'.$u.'</a></td></tr>';
echo '</table>';
echo '</div>';

if(!empty($g_user))
{
    echo '<div style="float: left; padding: 3px;">';
    echo '<table border="1" style="border-collapse: none;">';
    echo '<tr><th>select submission</th><th>lab</lab></tr>';
    foreach($dirs as $d => $details)
        if($g_dir == $d)
            echo '<tr style="background: #ddd"><td><a href="?user='.$g_user.'&dir='.$d.'"><strong>'.date("Y-m-d H:i:s (T)", intval($d)).' ('.$d.')</strong></a></td><td>'.$details["lab"].'</td></tr>';
        else
            echo '<tr><td><a href="?user='.$g_user.'&dir='.$d.'">'.date("Y-m-d H:i:s (T)", intval($d)).' ('.$d.')</a></td><td>'.$details["lab"].'</td></tr>';
    echo '</table>';
    echo '<br />';
    echo '<form method="post" action="?user='.$g_user.'" enctype="multipart/form-data">';
    echo '<strong>upload new file</strong><br />';
    echo 'submission archive: <input type="file" name="file" /><br />';
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
    echo '<tr><th>select file</th><th>size (bytes)</th><th>mimetype</th><th>download</th></tr>';
    foreach($files as $f => $details)
        if($g_file == $f)
            echo '<tr style="background: #ddd"><td><a href="?user='.$g_user.'&dir='.$g_dir.'&file='.$f.'"><strong>'.$f.'</strong></a></td><td>'.$details["size"].'</td><td>'.$details["mime"].'</td><td><a href="?user='.$g_user.'&dir='.$g_dir.'&file='.$f.'&dl=true">download</a></td></tr>';
        else
            echo '<tr><td><a href="?user='.$g_user.'&dir='.$g_dir.'&file='.$f.'">'.$f.'</a></td><td>'.$details["size"].'</td><td>'.$details["mime"].'</td><td><a href="?user='.$g_user.'&dir='.$g_dir.'&file='.$f.'&dl=true">download</a></td></tr>';
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
    echo '<pre>';
    if($files[$g_file]["mime"] != "text/plain")
	echo "file is not text/plain (use download link instead)";
    elseif($files[$g_file]["size"] >= 1024*20)
	echo "file larger than 20kiB (use download link instead)";
    else
	echo htmlentities(file_get_contents(SUBMISSION_FOLDER."/".$g_user."/".$g_dir."/".$g_file));
    echo '</pre>';
}



echo "<hr />logged in as user: ".$_SERVER["REMOTE_USER"]."; access for: ".implode(", ", $perms);

?>