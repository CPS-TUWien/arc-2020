<h1>ARC simulator</h1>
<?php

define("PERM_FILE", "/remotesim/.htperms");
define("SUBMISSION_FOLDER", "/remotesim/uploads/");

$labs = array("wall_follow");

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
		$dirs[$entry] = array("size" => $sz, "mime" => $mime);
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

if(!empty($_FILES) && !empty($g_user))
{
    print_r($FILES);

    $upload_time = time();
    $target_dir = SUBMISSION_FOLDER."/".$g_user."/".$upload_time;
    $target_file = $target_dir."/upload.zip";
    echo $target_file;
    @mkdir($target_dir, 0777, true);
    @chmod($target_dir, 0777);

    if (!move_uploaded_file($_FILES["file"]["tmp_name"], $target_file))
	die("could not move uploaded file.");
}

$dirs = get_submissions($g_user);
$g_dir = empty($_GET["dir"]) ? "" : $_GET["dir"];
if(!in_array($g_dir, array_keys($dirs)))
	$g_dir = "";
$files = get_submissions($g_user, $g_dir);
$g_file = empty($_GET["file"]) ? "" : $_GET["file"];
if(!in_array($g_file, array_keys($files)))
	$g_file = "";

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
    echo '<tr><th>select submission</th></tr>';
    foreach($dirs as $d => $details)
        if($g_dir == $d)
            echo '<tr style="background: #ddd"><td><a href="?user='.$g_user.'&dir='.$d.'"><strong>'.date("Y-m-d H:i:s (T)", intval($d)).' ('.$d.')</strong></a></td></tr>';
        else
            echo '<tr><td><a href="?user='.$g_user.'&dir='.$d.'">'.date("Y-m-d H:i:s (T)", intval($d)).' ('.$d.')</a></td></tr>';
    echo '</table>';
    echo '<br />';
    echo '<form method="post" action="?user='.$g_user.'" enctype="multipart/form-data">';
    echo '<strong>upload new file</strong><br />';
    echo '<input type="file" name="file" /><br />';
    echo '<input type="submit" value="upload file" />';
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