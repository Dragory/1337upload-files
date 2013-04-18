<?php

date_default_timezone_set('UTC');

$requestUri = $_SERVER['REQUEST_URI'];

// Remove the trailing slash from the requested URI if one is present
if (substr($requestUri, -1) == '/') $requestUri = substr($requestUri, 0, -1);

$requestParts = explode('/', $requestUri);
$requestFilename = array_pop($requestParts);

if (false !== ($queryPos = strpos($requestFilename, '?')))
    $requestFilename = substr($requestFilename, 0, $queryPos);

// exit($requestFilename);

if (empty($requestFilename)) exit;

session_start();

$settings = include __DIR__ . '/../settings.php';

// Our MySQL/MariaDB connection
$con = new PDO('mysql:host=' . $settings['db']['host'] . ';dbname=' . $settings['db']['database'],
    $settings['db']['username'],
    $settings['db']['password'],
    array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
    ));
$con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Make sure the filename is safe
$safeFilename = str_replace(
    ['/', '\\'],
    null,
    $requestFilename
);

$forceDownload = isset($_GET['force']);

// Check if there's a database record for the file
$query = $con->prepare("SELECT * FROM leetup_files WHERE filename = ?");
$query->execute([$safeFilename]);

$row = $query->fetch();

// Check if the file exists
if ( ! $row || ! file_exists($settings['filePath'] . '/' . $safeFilename))
{
    $timeToCache = 60 * 5;
    $expiryTime = time() + $timeToCache;

    $serveType = $settings['404']['type'];
    $length = filesize($settings['404']['path']);

    header("Content-Type: {$serveType}");
    header("Expires: " . gmdate("D, d M Y H:i:s", $expiryTime) . ' GMT');
    header("Pragma: cache");
    header("Cache-Control: max-age=" . $timeToCache);
    header("Content-Length: ".$length);

    ob_clean();
    flush();
    readfile($settings['404']['path']);
    exit;
}

$filenameParts = explode('.', $safeFilename);
$extension = strtolower(array_pop($filenameParts));

$fullPath = $settings['filePath'] . '/' . $safeFilename;
$length = $row['filesize'];

if (array_key_exists('HTTP_REFERER', $_SERVER))
    $referer = $_SERVER['HTTP_REFERER'];
else
    $referer = '';

// If the current client has no files viewed, create the SESSION variable
if ( ! isset($_SESSION['viewed_recently']))
    $_SESSION['viewed_recently'] = [];

// Update the file's views
if ( ! isset($_SESSION['viewed_recently'][$row['id']]))
{
    $query = $con->prepare("UPDATE leetup_files SET dls = dls + 1, referer = ? WHERE id = ?");
    $query->execute([
        $referer,
        $row['id']
    ]);

    $_SESSION['viewed_recently'][$row['id']] = true;
}

if ( ! $forceDownload && isset($settings['mediaTypes'][$extension]))
{
    $timeToCache = $settings['timeToCache'];
    $expiryTime = time() + $timeToCache;

    $serveType = $settings['mediaTypes'][$extension];

    header("Content-Type: {$serveType}");
    header("Expires: " . gmdate("D, d M Y H:i:s", $expiryTime) . ' GMT');
    header("Pragma: cache");
    header("Cache-Control: max-age=" . $timeToCache);
    header("Content-Length: " . $length);
}
else
{
    header("Content-Description: File Transfer");
    header("Content-Type: application/octet-stream");
    header('Content-Disposition: attachment; filename=' . $row['filename']);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $length);
}

ob_clean();
ob_end_flush();
@readfile($fullPath);