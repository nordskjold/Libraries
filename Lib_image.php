<?php

	class Lib_image extends Lib_base {
		
		private $file, $file_path, $file_name, $file_extension;
		
		private $file_width, $file_height, $file_new_width = null, $file_new_height = null;
		
		private $thumbs = array();
		
		function __construct() {}
		
		private function __clone() {}
		
		/**
		 * Initiates the image handler.
		 * 
		 * @param string $file
		 * <p>Absolut path from the root.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the image is initiated, <b>FALSE</b> if not.
		 */
		public function init($file) {
			$this->file = $this->root.trim($file, "/");
			
			if(! file_exists($this->file) || is_dir($this->file)) {
				return $this->addLibError("Filen kunne ikke findes.");
			}
			
			$this->file_path = explode("/", $this->file);
			$this->file_name = array_pop($this->file_path);
			$this->file_path = implode("/", $this->file_path);
			
			$this->file_name = explode('.', $this->file_name);
			$this->file_extension = strtolower(array_pop($this->file_name));
			$this->file_name = implode(".", $this->file_name);
			
			if(Helper_filegroup::isExtMemberOfFileGroup($this->file_extension, "image") === false) {
				return $this->addLibError("Fil typen er ikke understøttet.");
			}
			
			list($this->file_width, $this->file_height) = getimagesize($this->file);
			list($this->file_new_width, $this->file_new_height) = array($this->file_width, $this->file_height);
			
			return true;
		}
		
		/**
		 * Sets the image properties.
		 * 
		 * @param mixed $new_width [optional]
		 * <p>The new width of the image.</p>
		 * 
		 * @param mixed $new_height [optional]
		 * <p>The new height of the image.</p>
		 * 
		 * @param boolean $constrain_dimension [optional]
		 * <p>Constrain the image dimensions.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the properties is set, <b>FALSE</b> if not.
		 */
		public function setProperties($new_width = null, $new_height = null, $constrain_dimension = true) {
			if(! $this->isLibErrorsEmpty()) {
				return false;
			}
			
			list($this->file_new_width, $this->file_new_height) = $this->resizeDimensions($this->file_width, $this->file_height, $new_width, $new_height, $constrain_dimension);
			
			return true;
		}

		/**
		 * Creates a thumb of the image and sets the properties.
		 * 
		 * @param string $thumb_path
		 * <p>Absolut path from the root to the thumb folder.</p>
		 * 
		 * @param string $thumb_name [optional]
		 * <p>New name for the thumb, without file extension.</p>
		 * <p>Default: <b>'thumb_'</b> prepended in original image name.</p>
		 * 
		 * @param mixed $new_width [optional]
		 * <p>The new width of the image.</p>
		 * 
		 * @param mixed mixed $new_height [optional]
		 * <p>The new height of the image.</p>
		 * 
		 * @param boolean $constrain_dimension [optional]
		 * <p>Constrain the image dimensions.</p>
		 * 
		 * @return boolean <b>TRUE</b> if the thumb is created, <b>FALSE</b> if not.
		 */
		public function createThumb($thumb_path, $thumb_name = null, $thumb_new_width = null, $thumb_new_height = null, $constrain_dimension = true) {
			if(! $this->isLibErrorsEmpty()) {
				return false;
			}
			
			$thumb = array("file" => "", "file_path" => $this->root.trim($thumb_path, "/"), "file_name" => "", "file_extension" => "", "file_new_width" => "", "file_new_height" => "");
			
			if(! file_exists($thumb["file_path"]) || ! is_dir($thumb["file_path"])) {
				return $this->addLibError("Ugyldig sti.");
			}
			
			$thumb["file_name"] = ($thumb_name !== null ? $thumb_name : 'thumb_' .$this->file_name);
			$thumb["file_extension"] = $this->file_extension;
			$thumb["file"] = $thumb["file_path"].DIRECTORY_SEPARATOR.$thumb["file_name"]. '.' .$thumb["file_extension"];
			
			if(file_exists($thumb['file'])) {
				return $this->addLibError("Filen eksistere allerede.");
			}
			
			list($thumb['file_new_width'], $thumb['file_new_height']) = $this->resizeDimensions($this->file_width, $this->file_height, $thumb_new_width, $thumb_new_height, $constrain_dimension);
			
			$this->thumbs[] = $thumb;
			
			return true;
		}
		
		/**
		 * Applies the image changes.
		 * 
		 * @return boolean <b>TRUE</b> on success, <b>FALSE</b> if there's an image error.
		 */
		public function apply() {
			if(! $this->isLibErrorsEmpty()) {
				return false;
			}
			
			if(! empty($this->thumbs)) {
				foreach($this->thumbs as $thumb) {
					if(! $this->modifyImage($thumb)) {
						return false;
					}
				}
			}
			
			if($this->file_new_width !== $this->file_width && $this->file_new_height !== $this->file_height) {
				$image = array(
					"file" => $this->file,
					"file_path" => $this->file_path,
					"file_name" => $this->file_name,
					"file_extension" => $this->file_extension,
					"file_new_width" => $this->file_new_width,
					"file_new_height" => $this->file_new_height
				);

				if(! $this->modifyImage($image)) {
					return false;
				}
			}
			
			$this->clearObj();
			
			return true;
		}
		
		/**
		 * Modifies the images and sets the new properties.
		 * 
		 * @param array $image
		 * <p>The image details to modify from.</p>
		 * 
		 * <p>Image array example:</p>
		 * <p>
		 * <table>
		 * <tr valign="top"><td><b>KEY</b></td><td><b>VALUE</b></td></tr>
		 * <tr valign="top"><td>file_new_width</td><td>400</td></tr>
		 * <tr valign="top"><td>file_new_height</td><td>400</td></tr>
		 * <tr valign="top"><td>path</td><td>/images/</td></tr>
		 * <tr valign="top"><td>name</td><td>new_image</td></tr>
		 * </table>
		 * </p>
		 * 
		 * @return boolean <b>TRUE</b> if the image resize succeeded, <b>FALSE</b> if an error occured.
		 */
		private function modifyImage($image) {
			$img = imagecreatetruecolor($image["file_new_width"], $image["file_new_height"]);
			
			if($image["file_extension"] === "png") {
				$source = imagecreatefrompng($this->file);

				$info = $this->getPngImageInfo($this->file);
				$bit_depth = $info["channels"] * $info["bits"];

				if($bit_depth == 8) {
					$bga = imagecolorallocatealpha($img, 0, 0, 0, 127);
					imagecolortransparent($img, $bga);
					imagefill($img, 0, 0, $bga);
					imagecopyresampled($img, $source, 0, 0, 0, 0, $image["file_new_width"], $image["file_new_height"], $this->file_width, $this->file_height);
					imagetruecolortopalette($img, false, 255);
					imagesavealpha($img, true);
					imagepng($img, $image["file"]);
				} else {
					$transparency = imagecolortransparent($source);

					if($transparency >= 0) {
						$transparent_color = @imagecolorsforindex($source, $trnprt_indx);
						$transparency = @imagecolorallocate($img, $trnprt_color["red"], $trnprt_color["green"], $trnprt_color["blue"]);
						imagefill($img, 0, 0, $transparency);
						imagecolortransparent($img, $transparency);
					}

					imagealphablending($img, false);
					$color = imagecolorallocatealpha($img, 0, 0, 0, 127);
					imagefill($img, 0, 0, $color);
					imagesavealpha($img, true);
					imagecopyresampled($img, $source, 0, 0, 0, 0, $image["file_new_width"], $image["file_new_height"], $this->file_width, $this->file_height);
					imagepng($img, $image["file"]);
				}
			} elseif($image["file_extension"] === "gif") {
				$source = imagecreatefromgif($this->file);

				$transparency = imagecolortransparent($source);

				if($transparency >= 0) {
					$transparent_color = @imagecolorsforindex($source, $trnprt_indx);
					$transparency = @imagecolorallocate($img, $trnprt_color["red"], $trnprt_color["green"], $trnprt_color["blue"]);
					imagefill($img, 0, 0, $transparency);
					imagecolortransparent($img, $transparency);
				}

				imagecopyresampled($img, $source, 0, 0, 0, 0, $image["file_new_width"], $image["file_new_height"], $this->file_width, $this->file_height);
				imagegif($img, $image["file"]);
			} else {
				$source = imagecreatefromjpeg($this->file);
				imageinterlace($source, true);
				imagecopyresampled($img, $source, 0, 0, 0, 0, $image["file_new_width"], $image["file_new_height"], $this->file_width, $this->file_height);
				imagejpeg($img, $image["file"], 95);
			}

			imagedestroy($img);
			
			return true;
		}
		
		/**
		 * Global dimension resizer.
		 * 
		 * @param int $old_width
		 * <p>Width of original image.</p>
		 * 
		 * @param int $old_height
		 * <p>Height of original image.</p>
		 * 
		 * @param mixed $new_width [optional]
		 * <p>The new width of the image.</p>
		 * 
		 * @param mixed mixed $new_height [optional]
		 * <p>The new height of the image.</p>
		 * 
		 * @param boolean $constrain_dimension [optional]
		 * <p>Constrain the image dimensions.</p>
		 * <p>Default: <b>TRUE</b>.</p>
		 * 
		 * @return array The new width and new height.
		 * 
		 * <p>Dimension return array example:</p>
		 * <p>
		 * <table>
		 * <tr valign="top"><td><b>KEY</b></td><td><b>VALUE</b></td></tr>
		 * <tr valign="top"><td>0</td><td>200</td></tr>
		 * <tr valign="top"><td>1</td><td>100</td></tr>
		 * </table>
		 * </p>
		 */
		private function resizeDimensions($old_width, $old_height, $new_width = null, $new_height = null, $constrain_dimension = true) {
			$calced_new_width = $old_width;
			$calced_new_height = $old_height;
			
			if($new_width) {
				if($new_width < $calced_new_width) {
					$calced_new_width = $new_width;
					
					if($constrain_dimension) {
						$percent = round((($old_width - $new_width) / $old_width) * 100, 2);
						$calced_new_height = $old_height - round(($old_height * $percent) / 100);
					}
				}
			}
			
			if($new_height) {
				if($new_height < $calced_new_height) {
					$calced_new_height = $new_height;
					
					if($constrain_dimension) {
						$percent = round((($old_height - $new_height) / $old_height) * 100, 2);
						$calced_new_width = $old_width - round(($old_width * $percent) / 100);
					}
				}
			}
			
			return array((int)$calced_new_width, (int)$calced_new_height);
		}
		
		/**
		 * Retrieve PNG image informations.
		 * 
		 * @param string $file
		 * <p>Absolut path from the root to the PNG image.</p>
		 * 
		 * @return array|boolean PNG image info in an array, <b>FALSE</b> if info couldn't be retrieved.
		 * 
		 * <p>PNG info return array example:</p>
		 * <p>
		 * <table>
		 * <tr valign="top"><td><b>KEY</b></td><td><b>VALUE</b></td></tr>
		 * <tr valign="top"><td>width</td><td>400</td></tr>
		 * <tr valign="top"><td>height</td><td>400</td></tr>
		 * <tr valign="top"><td>bit-depth</td><td>8</td></tr>
		 * <tr valign="top"><td>color</td><td>6</td></tr>
		 * <tr valign="top"><td>compression</td><td>0</td></tr>
		 * <tr valign="top"><td>filter</td><td>0</td></tr>
		 * <tr valign="top"><td>interface</td><td>0</td></tr>
		 * <tr valign="top"><td>color-type</td><td>Truecolour with alpha</td></tr>
		 * <tr valign="top"><td>channels</td><td>4</td></tr>
		 * <tr valign="top"><td>bits</td><td>8</td></tr>
		 * </table>
		 * </p>
		 */
		private function getPngImageInfo($file) {
			if(empty($file)) {
				return false;
			}

			$info = unpack('A8sig/Nchunksize/A4chunktype/Nwidth/Nheight/Cbit-depth/'.'Ccolor/Ccompression/Cfilter/Cinterface',
			file_get_contents($file,0,null,0,29));

			if(empty($info)) {
				return false;
			}

			if("\x89\x50\x4E\x47\x0D\x0A\x1A\x0A" != array_shift($info)) {
				return false; // no PNG signature.
			}

			if(13 != array_shift($info)) {
				return false; // wrong length for IHDR chunk.
			}

			if('IHDR' !== array_shift($info)) {
				return false; // a non-IHDR chunk singals invalid data.
			}

			$color = $info['color'];

			$type = array(0 => 'Greyscale', 2 => 'Truecolour', 3 => 'Indexed-colour', 4 => 'Greyscale with alpha', 6 => 'Truecolour with alpha');

			if(empty($type[$color])) {
				return false; // invalid color value
			}

			$info['color-type'] = $type[$color];

			$samples = ((($color % 4) % 3) ? 3 : 1) + ($color > 3);

			$info['channels'] = $samples;
			$info['bits'] = $info['bit-depth'];

			return $info;
		}
		
		/**
		 * Clears the object for re-use.
		 */
		private function clearObj() {
			$this->file = null;
			$this->file_name = null;
			$this->file_extension = null;
			$this->file_width = null;
			$this->file_height = null;
			$this->file_new_width = null;
			$this->file_new_height = null;
			$this->thumbs = array();
		}
	}

?>