<?php

	//Process uploading image
	class ImageProcess {

		private $image; #Working image resource
		private $width; #Orginal image parameters
		private $height;

		//Constructor function, get image resource
		function __construct($imageString) {
			//Create image from string
			try {
				$image = imageCreateFromString(base64_decode($imageString));
				if (!$image)
					throw new Exception();
			} catch(Exception $e) {
				throw new Exception('Util::ImageProcess - Cannot create image, bad image string.');
			}
			//Save image resource and its parameters in this instance
			$this->image = $image;
			$this->width = imagesx($image);
			$this->height = imagesy($image);
		}

		//Get width and width
		public function getInfo() {
			return array(
				'width'		=> $this->width,
				'height'	=> $this->height
			);
		}

		//Add text
		public function addText($text,$x,$y,$size=12,$font='Aril',$color='') {
			//Process input parameter
			if ($x < 0)
				$x = $this->width + $x;
			if ($y < 0)
				$y = $this->hidth + $y;
			//Dev...
		}

		//Adding watermark, watermark has to be PNG
		public function addImage($watermarkFile,$x,$y) {
			//Process input parameter
			if ($x < 0)
				$x = $this->width + $x;
			if ($y < 0)
				$y = $this->height + $y;
			//Get watermark from file
			try {
				$watermark = imagecreatefrompng($watermarkFile);
				if (!$watermark)
					throw new Exception();
				$w = imagesx($watermark);
				$h = imagesy($watermark);
			} catch(Exception $e) {
				throw new Exception('Util::ImageProcess - Cannot process image, fail to load image file.');
			}
			//Put watermark in image
			try {
				if (!imagecopy($this->image,$watermark,$x,$y,0,0,$w,$h))
					throw new Exception();
			} catch(Exception $e) {
				throw new Exception('Util::ImageProcess - Cannot process image, fail to add image.');
			}
		}

		//Resize the image
		public function resize($newWidth,$newHeight) {
			//Resize (and resample as well) image
			try {
				$image = imagecreatetruecolor($newWidth,$newHeight);
				if (!imagecopyresampled($image,$this->image,0,0,0,0,$newWidth,$newHeight,$this->width,$this->height))
					throw new Exception();
			} catch(Exception $e) {
				throw new Exception('Util::ImageProcess - Cannot process image, fail to resize.');
			}
			//Refresh instance
			$this->image = $image;
			$this->width = imagesx($image);
			$this->height = imagesy($image);
		}

		//Render image
		//$result = $x->render('xx',00,null) return the result
		//$x->render('xx',00,'url') save the result
		public function render($quality=85,$filename=null,$format='jpeg') {
			//Input setting
			if ($filename)
				$save = true; #Save the file if filename provided
			else
				$save = false; #Return the content
			//Create/open working file
			try {
				if (!$filename)
					$filename = tempnam(sys_get_temp_dir(),'tmp');
				$file = fopen($filename,'r+');
			} catch(Exception $e) {
				throw new Exception('Util::ImageProcess - Cannot render image, fail to create temp working file.');
			}
			//Put image in file
			try {
				switch ($format) {
					case 'jpeg':
						if (!imagejpeg($this->image,$file,$quality))
							throw new Exception();
						break;
					case 'png':
						if (!imagepng($this->image,$file,
							$quality > 9 || $quality < 0 ? -1 : $quality
						))
							throw new Exception();
						break;
					case 'gif':
						if (!imagegif($this->image,$file))
							throw new Exception();
						break;
					default:
						throw new Exception();
				}
				
				fclose($file);
			} catch(Exception $e) {
				throw new Exception('Util::ImageProcess - Cannot render image, fail to render image in this type / cannot write to file.');
			}
			//Return
			if ($save)
				return; #Save the file, done
			try {
				$content = file_get_contents($filename); #Delete the temp file, return the content
				unlink($filename);
			} catch(Exception $e) {
				throw new Exception('Util::ImageProcess - Cannot render image, fail to seek the content.');
			}
			return $content;
		}

	}
	
	
	//Check data (string) type by regex
	function checkRegex($type,$string) {
		$check = array(
			'URL'		=> '/^[A-Za-z0-9_\-\:\/\.]{0,128}$/',
//			'SearchEngine'	=> '/spider|bot/i',
//			'Username'	=> '/^[A-Za-z0-9]{2,16}$/',
//			'Password'	=> '/^[a-f0-9]{32}$/',
//			'Nickname'	=> '/^[^~!@#$%^&*()_\+`\-=\|\\\\{\}\[\];:"\',.\/\<\>\?\s]{2,16}$/u',
			'Token'		=> '/^[A-Za-z0-9\/\+]{64}$/'
		);
		if (!isset($check[$type]))
			throw new Exception('Util::checkRegex - Type is undefined.');
		if (preg_match($check[$type],$string))
			return true;
		return false;
	}
	
	//Get the user input page by $_GET
	function getInputPage() {
		if (isset($_GET['page']) && ctype_digit($_GET['page'])) {
			$cp = intval($_GET['page']);
			if ($cp < 1)
				$cp = 1;
		}
		else $cp = 1;
		return $cp;
	}
?>
