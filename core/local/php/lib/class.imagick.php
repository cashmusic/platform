<?php
/**
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License Version 2.1
* @package Asido
* @subpackage Asido.Misc
* @version $Id: class.imagick.php 7 2007-04-09 21:09:09Z mrasnika $
*/

/////////////////////////////////////////////////////////////////////////////

/**
* Common file for all "Image Magick" based solutions which stores all the 
* supported file formats
*
* @package Asido
* @subpackage Asido.Misc
*/
Class Asido_IMagick {

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
	/**
	* Maps to supported mime types for saving files
	* @var array
	*/
	var $__mime = array(
	
		// support reading
		//
		'read' => array(
			
			// ART
			//
			'image/art',
			
			// BMP
			//
			'image/x-bmp',
			'image/x-bitmap',
			'image/x-xbitmap',
			'image/x-win-bitmap',
			'image/x-windows-bmp',
			'image/ms-bmp',
			'image/x-ms-bmp',
			'application/bmp',
			'application/x-bmp',
			'application/x-win-bitmap',
			'image/wbmp',
			'image/bmp',
			
			// CUT
			//
			'application/x-dr-halo-bitmap',
			'image/x-halo-cut',
			'zz-application/zz-winassoc-cut',
			'image/cut',
			'application/x-cut',
			'application/cut',
			
			// DCM
			//
			'image/dicom',
			'image/x-dicom',
			'x-lml/x-evm',
			
			// DCX
			//
			'image/dcx',
			'image/x-dcx',
			'image/x-pc-paintbrush',
			'image/vnd.swiftview-pcx',
			
			// DIB
			//
			'application/dib',
			'application/x-dib',
			'image/dib',
			'image/ms-bmp',
			'image/x-bmp',
			'image/x-ms-bmp',
			'image/x-win-bitmap',
			'image/x-xbitmap',
			'zz-application/zz-winassoc-dib',
			
			// DJVU
			//
			'image/vnd.djvu',
			'image/djvu',
			'image/x-djvu',
			'image/dejavu',
			'image/x-dejavu',
			'image/djvw',
			'image/x.djvu',
			
			// DNG
			//
			'application/x-ding',

			// DPX
			//
			'image/dpx',
			
			// FAX
			//
			'application/x-fax',
			'image/fax',
			'image/g3fax',
			'image/x-fax',

			// FITS
			//
			'application/x-fits',
			'application/fits',
			'image/x-fits',
			'image/fits',

			// GIF
			//
			'application/x-gif',
			'application/gif',
			'image/gif',
			'image/x-gif',
			
			// JPEG
			//
			'application/jpg',
			'application/x-jpg',
			'image/pjpeg',
			'image/pipeg',
			'image/jpg',
			'image/jpeg',

			// ICO
			//
			'image/x-icon',
			'application/ico',
			'application/x-ico',
			'image/ico',
			
			// MTV
			//
			'application/x-mtv',
			'image/x-mtv',
			
			// OTB
			//
			'image/x-otb',
			
			// P7
			//
			'application/x-xv-thumbnail',
			
			// PALM
			//
			'application/x-palm',
			'image/x-palm',
			
			// PBM
			//
			'application/x-portable-bitmap',
			'image/x-portable-bitmap',
			'image/x-portable-anymap',
			'image/x-portable/anymap',
			
			// PCD
			//
			'application/pcd',
			'application/x-photo-cd',
			'image/pcd',
			'image/x-photo-cd',

			// PCX
			//
			'image/pcx',
			'application/pcx',
			'application/x-pcx',
			'image/x-pc-paintbrush',
			'image/x-pcx',
			'zz-application/zz-winassoc-pcx',
			
			// PGM
			//
			'image/x-portable-graymap',
			'image/x-pgm',
			
			// PICT
			//
			'image/pict',
			'image/x-macpict',
			'image/x-pict',
			'image/x-quicktime',
			'image/x-quicktime',
			
			// PNG
			//
			'application/png',
			'application/x-png',
			'image/x-png',
			'image/png',
			
			// RLA
			//
			'application/x-rla-image',

			// SVG
			//
			'image/svg-xml',
			'text/xml-svg',
			'image/vnd.adobe.svg+xml',
			'image/svg-xml',
			'image/svg',
			
			// TGA
			//
			'application/tga',
			'application/x-tga',
			'application/x-targa',
			'image/tga',
			'image/x-tga',
			'image/targa',
			'image/x-targa',

			// TIFF
			//
			'image/x-tif',
			'image/x-tiff',
			'application/tif',
			'application/x-tif',
			'application/tiff',
			'application/x-tiff',
			'image/tif',
			'image/tiff',
			
			// WPG
			//
			'application/wpg',
			'application/x-wpg',
			'image/wpg',
			'image/x-wpg',
			'image/x-wordperfect-graphics',
			'application/x-wpg-viewer',
			'zz-application/zz-winassoc-wpg',
			
			// XPM
			//
			'image/x-xpixmap',
			'image/x-xpm',
			
			// XBM
			//
			'image/x-xbitmap',
			'image/x-xbm',
			
			// XCF
			//
			'application/xcf',
			'application/x-xcf',
			'image/xcf',
			'image/x-xcf',
			'application/x-gimp-image',

			),

		// support writing
		//
		'write' => array(
		
			// AVS
			//
			'application/x-stardent-avs',
		
			// BMP
			//
			'image/x-bmp',
			'image/x-bitmap',
			'image/x-xbitmap',
			'image/x-win-bitmap',
			'image/x-windows-bmp',
			'image/ms-bmp',
			'image/x-ms-bmp',
			'application/bmp',
			'application/x-bmp',
			'application/x-win-bitmap',
			'image/wbmp',
			'image/bmp',
			
			// CIN
			//
			'image/x-cin',

			// CMYK
			//
			'image/x-cmyk',

			// DCX
			//
			'image/dcx',
			'image/x-dcx',
			'image/x-pc-paintbrush',
			'image/vnd.swiftview-pcx',
			
			// DPX
			//
			'image/dpx',
			
			// FAX
			//
			'application/x-fax',
			'image/fax',
			'image/g3fax',
			'image/x-fax',
			
			// FITS
			//
			'application/x-fits',
			'application/fits',
			'image/x-fits',
			'image/fits',

			// GIF
			//
			'image/gif',
			
			// JPEG
			//
			'application/jpg',
			'application/x-jpg',
			'image/pjpeg',
			'image/pipeg',
			'image/jpg',
			'image/jpeg',
			
			// MTV
			//
			'application/x-mtv',
			'image/x-mtv',

			// OTB
			//
			'image/x-otb',

			// P7
			//
			'application/x-xv-thumbnail',

			// PALM
			//
			'application/x-palm',
			'image/x-palm',

			// PBM
			//
			'application/x-portable-bitmap',
			'image/x-portable-bitmap',
			'image/x-portable-anymap',
			'image/x-portable/anymap',

			// PCD
			//
			'application/pcd',
			'application/x-photo-cd',
			'image/pcd',
			'image/x-photo-cd',

			// PCX
			//
			'image/pcx',
			'application/pcx',
			'application/x-pcx',
			'image/x-pc-paintbrush',
			'image/x-pcx',
			'zz-application/zz-winassoc-pcx',
			
			// PDF
			//
			'application/pdf',
			'application/x-pdf',
			'application/acrobat',
			'applications/vnd.pdf',
			'text/pdf',
			'text/x-pdf',
			
			// PGM
			//
			'image/x-portable-graymap',
			'image/x-pgm',
			
			// PICT
			//
			'image/pict',
			'image/x-macpict',
			'image/x-pict',
			'image/x-quicktime',
			'image/x-quicktime',
			
			// PNG
			//
			'application/png',
			'application/x-png',
			'image/x-png',
			'image/png',

			// SVG
			//
			'image/svg-xml',
			'text/xml-svg',
			'image/vnd.adobe.svg+xml',
			'image/svg-xml',
			'image/svg',

			// TGA
			//
			'application/tga',
			'application/x-tga',
			'application/x-targa',
			'image/tga',
			'image/x-tga',
			'image/targa',
			'image/x-targa',

			// TIFF
			//
			'image/x-tif',
			'image/x-tiff',
			'application/tif',
			'application/x-tif',
			'application/tiff',
			'application/x-tiff',
			'image/tif',
			'image/tiff',
			
			// XPM
			//
			'image/x-xpixmap',
			'image/x-xpm',
			
			// XBM
			//
			'image/x-xbitmap',
			'image/x-xbm',

			)
		);

	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	/**
	* MIME-type to image format map
	*
	* This is used for conversion and saving ONLY, so  
	* read-only file formats should not appear here
	*
	* @var array
	*/
	var $__mime_map = array(

		// AVS
		//
		'application/x-stardent-avs' => 'AVS',

		// CIN
		//
		'image/x-cin' => 'CIN',
		
		// DCX
		//
		'image/dcx' => 'DCX',
		'image/x-dcx' => 'DCX',
		'image/x-pc-paintbrush' => 'DCX',
		'image/vnd.swiftview-pcx' => 'DCX',
		
		// DPX
		//
		'image/dpx' => 'DPX',
		
		// FAX
		//
		'application/x-fax' => 'FAX',
		'image/fax' => 'FAX',
		'image/g3fax' => 'FAX',
		'image/x-fax' => 'FAX',
		
		// FITS
		//
		'application/x-fits' => 'FITS',
		'application/fits' => 'FITS',
		'image/x-fits' => 'FITS',
		'image/fits' => 'FITS',
		
		// MTV
		//
		'application/x-mtv' => 'MTV',
		'image/x-mtv' => 'MTV',

		// OTB
		//
		'image/x-otb' => 'OTB',

		// P7
		//
		'application/x-xv-thumbnail' => 'P7',

		// PALM
		//
		'application/x-palm' => 'PALM',
		'image/x-palm' => 'PALM',

		// PBM
		//
		'application/x-portable-bitmap' => 'PBM',
		'image/x-portable-bitmap' => 'PBM',
		'image/x-portable-anymap' => 'PBM',
		'image/x-portable/anymap' => 'PBM',
		
		// PCX
		//
		'image/pcx' => 'PCX',
		'application/pcx' => 'PCX',
		'application/x-pcx' => 'PCX',
		'image/x-pc-paintbrush' => 'PCX',
		'image/x-pcx' => 'PCX',
		'zz-application/zz-winassoc-pcx' => 'PCX',
		
		// PDF
		//
		'application/pdf' => 'PDF',
		'application/x-pdf' => 'PDF',
		'application/acrobat' => 'PDF',
		'applications/vnd.pdf' => 'PDF',
		'text/pdf' => 'PDF',
		'text/x-pdf' => 'PDF',
		
		// PGM
		//
		'image/x-portable-graymap' => 'PGM',
		'image/x-pgm' => 'PGM',
		
		// PICT
		//
		'image/pict' => 'PICT',
		'image/x-macpict' => 'PICT',
		'image/x-pict' => 'PICT',
		'image/x-quicktime' => 'PICT',
		'image/x-quicktime' => 'PICT',

		// TIFF
		//
		'image/x-tif' => 'TIFF',
		'image/x-tiff' => 'TIFF',
		'application/tif' => 'TIFF',
		'application/x-tif' => 'TIFF',
		'application/tiff' => 'TIFF',
		'application/x-tiff' => 'TIFF',
		'image/tif' => 'TIFF',
		'image/tiff' => 'TIFF',
		
		// GIF
		//
		'image/gif' => 'GIF',
		
		// JPEG
		//
		'application/jpg' => 'JPEG',
		'application/x-jpg' => 'JPEG',
		'image/pjpeg' => 'JPEG',
		'image/pipeg' => 'JPEG',
		'image/jpg' => 'JPEG',
		'image/jpeg' => 'JPEG',

		// BMP
		//
		'image/x-bmp' => 'BMP',
		'image/x-bitmap' => 'BMP',
		'image/x-xbitmap' => 'BMP',
		'image/x-win-bitmap' => 'BMP',
		'image/x-windows-bmp' => 'BMP',
		'image/ms-bmp' => 'BMP',
		'image/x-ms-bmp' => 'BMP',
		'application/bmp' => 'BMP',
		'application/x-bmp' => 'BMP',
		'application/x-win-bitmap' => 'BMP',
		'image/wbmp' => 'BMP',
		'image/bmp' => 'BMP',

		// SVG
		//
		'image/svg-xml' => 'SVG',
		'text/xml-svg' => 'SVG',
		'image/vnd.adobe.svg+xml' => 'SVG',
		'image/svg-xml' => 'SVG',
		'image/svg' => 'SVG',

		// ICO
		//
		'image/x-icon' => 'ICO',
		'application/ico' => 'ICO',
		'application/x-ico' => 'ICO',
		'image/ico' => 'ICO',

		// PSD
		//
		'image/x-photoshop' => 'PSD',
		'image/psd' => 'PSD',
		'application/photoshop' => 'PSD',
		'application/psd' => 'PSD',
		'image/photoshop' => 'PSD',

		// PNG
		//
		'application/png' => 'PNG',
		'application/x-png' => 'PNG',
		'image/x-png' => 'PNG',
		'image/png' => 'PNG',

		// XPM
		//
		'image/x-xpixmap' => 'XPM',
		'image/x-xpm' => 'XPM',
		
		// XBM
		//
		'image/x-xbitmap' => 'XBM',
		'image/x-xbm' => 'XBM',

		);
	
	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
	
//--end-of-class--	
}

/////////////////////////////////////////////////////////////////////////////

?>