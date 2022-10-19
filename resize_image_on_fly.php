<?php
/*
- image resize on fly
- arguments:
    - i: image path in defined path
    - w: output image width
    - h: output image height
- request method: GET
*/

define( 'MEDIAS_DIR', 'medias/'  );
define( 'EMPTY_IMG', 'empty.png'  );

/*
    resizeImage function:
*/
function resizeImage( $image, $w = 800, $h = 450 ) {
    $ratio_thumb = $w/$h;
    list($xx, $yy) = getimagesize( $image );
    $ratio_original = $xx/$yy;
    if ( $ratio_original >= $ratio_thumb ) {
        $yo = $yy;
        $xo = ceil( ( $yo * $w ) / $h );
        $xo_ini = ceil( ( $xx - $xo ) / 2 );
        $xy_ini = 0;
    } else {
        $xo = $xx;
        $yo = ceil( ( $xo * $h ) / $w );
        $xy_ini = ceil( ( $yy - $yo ) / 2 );
        $xo_ini = 0;
    }

    // Create a new image instance
    $tn = imagecreatetruecolor($w, $h) ;

    $imgage_info = getimagesize( $image );

    switch ( $imgage_info[ 'mime' ] ) {

    	case 'image/jpeg':
            $image = imagecreatefromjpeg( $image ) ;
            imagecopyresampled($tn, $image, 0, 0, $xo_ini, $xy_ini, $w, $h, $xo, $yo) ;
            echo imagejpeg($tn);

            // Free from memory
            imagedestroy($tn);
    	break;

    	case 'image/gif':
    		$image = imagecreatefromgif( $image );

            // Make the background white
            imagefilledrectangle($tn, 0, 0, 99, 99, 0xFFFFFF);

            imagecopyresampled($tn, $image, 0, 0, $xo_ini, $xy_ini, $w, $h, $xo, $yo) ;
            echo imagegif($tn);

            // Free from memory
            imagedestroy($tn);
    	break;

    	case 'image/png':
            // Make the background transparent
            $background = imagecolorallocate($tn , 0, 0, 0);
            imagecolortransparent($tn, $background);
            imagealphablending($tn, false);
            imagesavealpha($tn, true);

            $image = imagecreatefrompng( $image );
            imagecopyresampled($tn, $image, 0, 0, $xo_ini, $xy_ini, $w, $h, $xo, $yo) ;
            imagepng($tn);

            // Free from memory
            //imagedestroy($tn);
    	break;
    	default:
    		die('Invalid image type');
    }
}

$image = MEDIAS_DIR . EMPTY_IMG;
if( isset( $_GET[ 'i' ] ) and file_exists( MEDIAS_DIR . $_GET[ 'i' ] ) ) {
    $image_temp = MEDIAS_DIR . $_GET[ 'i' ];
    if( @is_array( getimagesize( $image_temp ) ) ) {
        $image = $image_temp;
    }
}

$imgage_info = getimagesize( $image );

header( "Content-type: {$imgage_info[ 'mime' ]}" );
header( "Content-Length: " . filesize( $image ) );
if( isset( $_GET[ 'w' ] ) and ( $_GET[ 'w' ] > 0 ) and isset( $_GET[ 'h' ] ) and ( $_GET[ 'h' ] > 0 ) ) {
    resizeImage( $image, intval( $_GET[ 'w' ] ),  intval( $_GET[ 'h' ] ) );
} else {
    readfile( $image );
}
