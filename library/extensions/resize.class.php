<?php
/**
 * Supernova Framework
 */
/**
 * Image Resize Handler
 * @package MVC_Model_Resize
 */

Class Resize{
	
	/**
	 * Image itself
	 * @var object
	 */
	private $image;
	
	/**
	 * Image width
	 * @var Integer
	 */
	private $width;
	
	/**
	 * Image height
	 * @var Integer
	 */
	private $height;
	
	/**
	 * Path to save image
	 * @var String
	 */
	private $basePath;
	
	/**
	 * Image type
	 * @var String
	 */
	private $extension;
	
	/**
	 * Image resized
	 * @var object
	 */
	private $imageResized;

	/**
	 * Create thumbnails for image
	 * @param array $args Kind of thumbnail images
	 * @param string $fileName original image
	 * @return array $ret Paths with the new thumbnails
	 */
	function createThumbs($args, $fileName){
		if (is_array($fileName['file'])){
			$count = count($fileName['file']);
			for ($each = 0; $each < $count; $each++){
				$this->image = $this->openImage($fileName['file'][$each],$fileName['type'][$each]);
				$this->width  = imagesx($this->image);
				$this->height = imagesy($this->image);
				$pathArray = explode('/', $_SERVER["DOCUMENT_ROOT"]."/public/".$fileName['file'][$each]);
				$fisicalFileName = $pathArray[count($pathArray)-1];
				$pathArray[count($pathArray)-1] = null;
				$this->basePath = implode('/',$pathArray);
				foreach($args as $key => $arg){
					if (!file_exists($this->basePath.$key.'/')){
						$create = mkdir($this->basePath.$key.'/', 0777, TRUE);
					}
					$argWidth = ($arg['width']) ? $arg['width'] : -1 ;
					$argHeight = ($arg['height']) ? $arg['height'] : -1 ;
					$this->resizeImage($argWidth, $argHeight, $arg['type'][$each]);
					$ret[$key] = str_replace($_SERVER["DOCUMENT_ROOT"]."/public/", '',$this->saveImage($this->basePath.$key.'/'.$key.'.'.$fisicalFileName, 100));
				}
			}
		}else{
			$this->image = $this->openImage($fileName['file'],$fileName['type']);
			$this->width  = imagesx($this->image);
			$this->height = imagesy($this->image);
			$pathArray = explode('/', $_SERVER["DOCUMENT_ROOT"]."/public/".$fileName['file']);
			$fisicalFileName = $pathArray[count($pathArray)-1];
			$pathArray[count($pathArray)-1] = null;
			$this->basePath = implode('/',$pathArray);
			foreach($args as $key => $arg){
				if (!file_exists($this->basePath.$key.'/')){
					$create = mkdir($this->basePath.$key.'/', 0777, TRUE);
				}
				$argWidth = ($arg['width']) ? $arg['width'] : -1 ;
				$argHeight = ($arg['height']) ? $arg['height'] : -1 ;
				$this->resizeImage($argWidth, $argHeight, $arg['type']);
				$ret[$key] = str_replace($_SERVER["DOCUMENT_ROOT"]."/public/", '',$this->saveImage($this->basePath.$key.'/'.$key.'.'.$fisicalFileName, 100));
			}
		}
		return $ret;
	}
	
	/**
	 * Open file image
	 * @param string $file Filename
	 * @param string $type File type
	 * @return object
	 */
	private function openImage($file,$type){
		if (file_exists($_SERVER["DOCUMENT_ROOT"]."/public/".$file)){
			$file = $_SERVER["DOCUMENT_ROOT"]."/public/".$file;
		}
		// *** Get extension
		$extension = strtolower(strrchr($file, '.'));
		$this->extension = $extension;
		switch($type){
			case 'image/jpeg':
				$img = imagecreatefromjpeg($file);
				break;
			case 'image/gif':
				$img = imagecreatefromgif($file);
				break;
			case 'image/png':
				$img = imagecreatefrompng($file);
				imagealphablending($img, false);
				imagesavealpha($img, true);
				break;
			default:
				$img = false;
				break;
		}
		return $img;
	}

	/**
	 * Resize image
	 * @param int $newWidth new width image
	 * @param int $newHeight new height image
	 * @param string $option 
	 */
	public function resizeImage($newWidth = -1, $newHeight = -1, $option="auto")
	{
		// *** Get proportions if -1 *** //
		if ($newWidth == -1){
			if ($this->width > $this->height) { 
				$percentage = ($newHeight / $this->width); 
			} else { 
				$percentage = ($this->width / $newHeight); 
			}
			$newWidth = round($this->width * $percentage); 
		}
		
		if ($newHeight == -1){
			if ($this->width > $this->height) { 
				$percentage = ($newHeight / $this->width); 
			} else { 
				$percentage = ($this->width / $newHeight); 
			}
			$newHeight = round($this->height * $percentage); 
		}
		
		// *** Get optimal width and height - based on $option
		$optionArray = $this->getDimensions($newWidth, $newHeight, $option);

		$optimalWidth  = $optionArray['optimalWidth'];
		$optimalHeight = $optionArray['optimalHeight'];
		
		// *** Resample - create image canvas of x, y size
		$this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
		if($this->extension == '.png'){
			$trans = imagecolorallocatealpha($this->imageResized, 0, 0, 0, 127);
			imagefill($this->imageResized, 0, 0, $trans);	
		}
		imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);
		
		
		// *** if option is 'crop', then crop too
		if ($option == 'crop') {
			$this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
		}
	}

	/**
	 * Get image dimensions
	 * @param int $newWidth 
	 * @param int $newHeight 
	 * @param string $option 
	 * @return array
	 */
	private function getDimensions($newWidth, $newHeight, $option)
	{
		
	   switch ($option)
		{
			case 'exact':
				$optimalWidth = $newWidth;
				$optimalHeight= $newHeight;
				break;
			case 'portrait':
				$optimalWidth = $this->getSizeByFixedHeight($newHeight);
				$optimalHeight= $newHeight;
				break;
			case 'landscape':
				$optimalWidth = $newWidth;
				$optimalHeight= $this->getSizeByFixedWidth($newWidth);
				break;
			case 'auto':
				$optionArray = $this->getSizeByAuto($newWidth, $newHeight);
				$optimalWidth = $optionArray['optimalWidth'];
				$optimalHeight = $optionArray['optimalHeight'];
				break;
			case 'crop':
				$optionArray = $this->getOptimalCrop($newWidth, $newHeight);
				$optimalWidth = $optionArray['optimalWidth'];
				$optimalHeight = $optionArray['optimalHeight'];
				break;
			default:$optionArray = $this->getSizeByAuto($newWidth, $newHeight);
				$optimalWidth = $optionArray['optimalWidth'];
				$optimalHeight = $optionArray['optimalHeight'];
				break;
		}
		return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
	}

	/**
	 * Get image size with fixed Height
	 * @param int $newHeight 
	 * @return int New width
	 */
	private function getSizeByFixedHeight($newHeight)
	{
		$ratio = $this->width / $this->height;
		$newWidth = $newHeight * $ratio;
		return $newWidth;
	}

	/**
	 * Get image size with fixed Width
	 * @param int $newWidth 
	 * @return int New height
	 */
	private function getSizeByFixedWidth($newWidth)
	{
		$ratio = $this->height / $this->width;
		$newHeight = $newWidth * $ratio;
		return $newHeight;
	}

	/**
	 * Get image size automatic
	 * @param int $newWidth 
	 * @param int $newHeight 
	 * @return array New width and new height
	 */
	private function getSizeByAuto($newWidth, $newHeight)
	{
		if ($this->height < $this->width)
		// *** Image to be resized is wider (landscape)
		{
			$optimalWidth = $newWidth;
			$optimalHeight= $this->getSizeByFixedWidth($newWidth);
		}
		elseif ($this->height > $this->width)
		// *** Image to be resized is taller (portrait)
		{
			$optimalWidth = $this->getSizeByFixedHeight($newHeight);
			$optimalHeight= $newHeight;
		}
		else
		// *** Image to be resizerd is a square
		{
			if ($newHeight < $newWidth) {
				$optimalWidth = $newWidth;
				$optimalHeight= $this->getSizeByFixedWidth($newWidth);
			} else if ($newHeight > $newWidth) {
				$optimalWidth = $this->getSizeByFixedHeight($newHeight);
				$optimalHeight= $newHeight;
			} else {
				// *** Sqaure being resized to a square
				$optimalWidth = $newWidth;
				$optimalHeight= $newHeight;
			}
		}
		
		return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
	}

	/**
	 * Get optimal Crop
	 * 
	 * @param int $newWidth 
	 * @param int $newHeight 
	 * @return array
	 */
	private function getOptimalCrop($newWidth, $newHeight)
	{
		
		$heightRatio = $this->height / $newHeight;
		$widthRatio  = $this->width /  $newWidth;
		
		if ($heightRatio < $widthRatio) {
			$optimalRatio = $heightRatio;
		} else {
			$optimalRatio = $widthRatio;
		}
		
		$optimalHeight = $this->height / $optimalRatio;
		$optimalWidth  = $this->width  / $optimalRatio;
		
		return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
	}

	/**
	 * Crop function
	 * @param int $optimalWidth 
	 * @param int $optimalHeight 
	 * @param int $newWidth 
	 * @param int $newHeight 
	 */
	private function crop($optimalWidth, $optimalHeight, $newWidth, $newHeight)
	{
		// *** Find center - this will be used for the crop
		$cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
		$cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );
		
		$crop = $this->imageResized;
		//imagedestroy($this->imageResized);
		
		// *** Now crop from center to exact requested size
		$this->imageResized = imagecreatetruecolor($newWidth , $newHeight);
		if($this->extension == '.png'){
			$trans = imagecolorallocatealpha($this->imageResized, 0, 0, 0, 127);
			imagefill($this->imageResized, 0, 0, $trans);	
		}
		imagecopyresampled($this->imageResized, $crop , 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
	}

	/**
	 * Save image
	 * @param string $savePath Path to save image
	 * @param string $imageQuality Image quality percentage
	 * @return string Returns path to image
	 */
	public function saveImage($savePath, $imageQuality="100")
	{
		// *** Get extension
	$extension = strrchr($savePath, '.');
	$extension = strtolower($extension);
		switch($extension)
		{
			case '.jpg':
			case '.jpeg':
				if (imagetypes() & IMG_JPG) {
					imagejpeg($this->imageResized, $savePath, $imageQuality);
				}
				break;
				
			case '.gif':
				if (imagetypes() & IMG_GIF) {
					imagegif($this->imageResized, $savePath);
				}
				break;
				
			case '.png':
				// *** Scale quality from 0-100 to 0-9
				$scaleQuality = round(($imageQuality/100) * 9);
				
				// *** Invert quality setting as 0 is best, not 9
				$invertScaleQuality = 9 - $scaleQuality;
				
				if (imagetypes() & IMG_PNG) {
					imagepng($this->imageResized, $savePath, $invertScaleQuality);
				}
				break;
				
			default:
				// *** No extension - No save.
				break;
		}
		imagedestroy($this->imageResized);
		return $savePath;
	}

}
?>
