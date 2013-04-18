<?php
    date_default_timezone_set("UTC");

    ini_set('display_errors', 'On');
    error_reporting(E_ALL);

    // SETTINGS
    $targetWidth    = 150;
    $targetHeight   = 150;
    
    $barHeight = 16;
    
    $fontDir = __DIR__ . '/../fonts/';
    $fontName = 'verdana.ttf';
    $fontSize = 9;
    
    $cacheTime = 60; // In minutes
    $cacheTime *= 60; // <-- Changes it to seconds for the timestamp comparison
    
    // END SETTINGS

    // Remove the trailing slash from the requested URI if one is present
    $requestUri = $_SERVER['REQUEST_URI'];
    if (substr($requestUri, -1) == '/') $requestUri = substr($requestUri, 0, -1);

    $requestParts = explode('/', $requestUri);
    $requestFilename = array_pop($requestParts);

    if (empty($requestFilename)) exit;

    $barEnabled = (isset($_GET["b"]) ? false : true);
    if(!$barEnabled) $barHeight = 0;
    
    $replace = array(
        '/' => '',
        '..' => '',
        '\\' => '',
    );
    
    $file = $requestFilename;
    $file = str_replace( array_keys($replace), array_values($replace), $file );
    
    if(!file_exists( __DIR__ . '/../files/'.$file )) {
        echo 'File does not exist.';
        exit;
    }
    
    $filenameParts = explode('.', $file);
    $extension = strtolower(end($filenameParts));
    array_pop($filenameParts);
    $noextension = implode('', $filenameParts);
    
    $thumbFullpath = __DIR__ . '/../thumbnails/'.$noextension.'_thumb'.(!$barEnabled ? "_nobar" : "").'.png';

    // echo "thumbnails/{$noextension}_thumb".(!$barEnabled ? "_nobar" : "").".png"; exit;

    if(file_exists( $thumbFullpath ) && time()-filemtime($thumbFullpath) > 60*60) {
        /*header('Status: 301');
        header('Location: '.$settings["urlBase"].'/thumbnails/'.$noextension.'_thumb'.(!$barEnabled ? "_nobar" : "").'.png');*/
        $expireTime = time() + 60 * 60; // 60 minutes = 1 hour.

        header("Content-Type: image/png");
        header("Expires: ".date("D, d M Y H:i:s GMT", $expireTime));
        header("Content-Length: ".filesize( $thumbFullpath ));

        ob_clean();
        flush();
        readfile($thumbFullpath);
        exit;
    }
    
    $file = __DIR__ . '/../files/' . $file;
    list($width, $height) = getimagesize( $file );
    
    $sizeModded = false;
    if($width < $targetWidth) { $targetWidth = $width; $sizeModded = true; }
    if($height < $targetHeight && $height > 24) { $targetHeight = $height; $sizeModded = true; }
    
    if(!$sizeModded) $targetHeight *= ($height / $width);
    if($targetHeight > 200) $targetHeight = 200;
    
    $targetHeight += $barHeight;
    
    $im_base = imagecreatetruecolor( $targetWidth, $targetHeight );
    
    switch($extension) {
        case 'png':
            //header('Content-type: image/png');
            $im_old = imagecreatefrompng( $file );
            break;
        case 'jpg':
            //header('Content-type: image/jpeg');
            $im_old = imagecreatefromjpeg( $file );
            break;
        case 'jpeg':
            //header('Content-type: image/jpeg');
            $im_old = imagecreatefromjpeg( $file );
            break;
        case 'gif':
            //header('Content-type: image/gif');
            $im_old = imagecreatefromgif( $file );
            break;
        default:
            exit;
            break;
    }
    
    // header('Content-type: image/png');
    
    imagecopyresampled( $im_base, $im_old, 0, 0, 0, 0, $targetWidth, $targetHeight-$barHeight, $width, $height );
    if($barEnabled) 
    {
        $textSize = imagettfbbox( $fontSize, 0, $fontDir.$fontName, $width.'x'.$height );
        //print_r($textSize); exit;
        
        $halfWidth = $targetWidth/2-$textSize[4]/2;
        $halfHeight = ($targetHeight-$barHeight)+($barHeight/2-$textSize[7]/2);
        $col = imagecolorallocate( $im_base, 255, 255, 255 );
        
        imagettftext( $im_base, $fontSize, 0, $halfWidth, $halfHeight, $col, $fontDir.$fontName, $width.'x'.$height );
    }

    imagepng( $im_base, $thumbFullpath );
    imagedestroy($im_base);
    imagedestroy($im_old);
    
    /*header('Status: 301');
    header('Location: '.$settings["urlBase"].'/thumbnails/'.$noextension.'_thumb'.(!$barEnabled ? "_nobar" : "").'.png');*/
    $expireTime = time() + 60 * 60; // 60 minutes = 1 hour.
    
    header("Content-Type: image/png");
    header("Expires: ".date("D, d M Y H:i:s GMT", $expireTime));
    header("Content-Length: ".filesize( $thumbFullpath ));

    ob_clean();
    flush();
    try {readfile($thumbFullpath);}catch(Exception $e) {echo ":d";}
    exit;
?>