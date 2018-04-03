<?php
ini_set('display_errors','On');
/*
 * Plugin Name: Image Effects
 * Plugin URI:  http://kevix.rf.gd/wordpress-plugin/
 * Description: Image Effects is plugin that is Image Processing Plugin which is used PHP ImageMagick Lib
 * Version:     1.0.0.beta
 * Author:      Kevin Lee Ka Chun <kevixli@yahoo.com.hk>
 * Author URI:  http://kevix.rf.gd/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: image_effects
 * Domain Path: /languages/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Image_effects_Plugin' ) ){
    class Image_effects_Plugin {

        private $image_effects_settings;
        /**
		 * Hold an instance of Image_effects_Plugin class.
		 *
		 * @var Image_effects_Plugin
		 */
        protected static $instance = null;
        

        /**
		 * Constructor
		 */
		public function __construct() {
			//$this->includes();
            //$this->hooks();
            $this->image_effects_settings = get_option( 'image_effects_settings' );
			$this->setup_shortcode();
        }


        /**
		 * Main Image_effects_Plugin instance.
		 * @return Image_effects_Plugin - Main instance.
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {
				self::$instance = new Image_effects_Plugin;
			}

			return self::$instance;
        }
        
        
        public static function activate_plugin() {

			// Don't activate on anything less than PHP 5.2.0 or WordPress 3.4 or No PHP Imagick Extenstion
			if ( version_compare( PHP_VERSION, ' 5.2.0', '<' ) || version_compare( get_bloginfo( 'version' ), '3.4', '<' ) || ! extension_loaded( 'imagick' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
				deactivate_plugins( basename( __FILE__ ) );
				wp_die( __( 'Image effects plugin requires PHP version 5.2.0 or greater with Imagick Extension and WordPress 3.4 or greater.', 'image_effects' ) );
			}

			include( 'inc/activate-plugin.php' );
        }
        

        /**
		 * Register the [image_effects] shortcode.
		 */
		private function setup_shortcode() {
			add_shortcode( 'image_effects', array( $this, 'register_shortcode' ) );
        }
        
        /**
		 * Shortcode used to display processed Image
		 *
		 * @return string HTML output of the shortcode
		 */
		public function register_shortcode( $atts ) {

			if ( ! isset( $atts['pic1'] ) || ! isset( $atts['style'] ) ) {
				return "Parameters (pic1, style) are required.";
            }
            
            $imageEffectStyle = strtolower(! isset( $atts['style'] ) ? "" : trim($atts['style']));

			$outputImgObj = null;
            // check if Image Effect exist and process
            switch($imageEffectStyle){
                case "text_on_image":
                    $outputImgObj = $this->writeTextOnImage(
                                        $atts['pic1'], 
                                        (isset($atts['text'])?$atts['text']:""), 
                                        (isset($atts['stroke-color'])?$atts['stroke-color']:"#000000"), 
                                        (isset($atts['fill-color'])?$atts['fill-color']:"#FFFFFF"),
                                        (isset($atts['font-offset-x'])?$atts['font-offset-x']:0), 
                                        (isset($atts['font-offset-y'])?$atts['font-offset-y']:0),
                                        (isset($atts['font-size'])?$atts['font-size']:36)
                                    );
                break;

                case "overlay_image":
                    $outputImgObj = $this->overlayImageEffect(
                                        $atts['pic1'], 
                                        $atts['pic2'],
                                        (isset($atts['offset-x'])?$atts['offset-x']:0), 
                                        (isset($atts['offset-y'])?$atts['offset-y']:0), 
                                        (isset($atts['resize-top-width'])?$atts['resize-top-width']:0), 
                                        (isset($atts['resize-top-height'])?$atts['resize-top-height']:0)
                                    );
                break;

                case "alpha_image":
                    $outputImgObj = $this->alphaImage(
                                        $atts['pic1'], 
                                        (isset($atts['opacity'])?$atts['opacity']:0.47)
                                    );
                break;

                case "scale_image":
                    $outputImgObj = $this->scaleImage(
                        $atts['pic1'], 
                        (isset($atts['width'])?$atts['width']:150),
                        (isset($atts['height'])?$atts['height']:150)
                    );
                break;

                case "imagick_composite":
                    $outputImgObj = $this->imagickComposite(
                        $atts['pic1'], 
                        $atts['pic2']
                    );
                break;
/*
                case "compsite_image":
                    $outputImgObj = $this->compsiteImages(
                                        $atts['pic1'],
                                        $atts['pic2'], 
                                        (isset($atts['type'])?imagick::${"COMPOSITE_".strtoupper($atts['type'])}:imagick::COMPOSITE_MULTIPLY  )
                                    );
                break;
*/
                default:
                    return "No this process image style (".$imageEffectStyle.") !";
                break;

            }

            return "<img src='".$this->returnImgUrlPath($outputImgObj)."' border='0' />";
		}

        public function thumbnail($imgPath, $max_width, $max_height) {

            $tmpFilePath = $this->grepImageFileFromURL($imgPath);

            $img = new Imagick($tmpFilePath);
            $img->thumbnailImage($max_width, $max_height, TRUE);

            $img->setImageFormat('png');
            return $img;
        }

        public function scaleImage($imgPath, $scaleWidth, $scaleHeight){
            $tmpFilePath = $this->grepImageFileFromURL($imgPath);
            $img = new Imagick($tmpFilePath);

            $img->scaleImage($scaleWidth, $scaleHeight, true);
            $img->setImageFormat('png');

            return $img;
        }


        public function writeTextOnImage($imgPath, $text, $strokeColor="#000000", $fillColor="#FFFFFF", $fontOffsetX=0, $fontOffsetY=0, $fontSize=36)
        {
            $tmpFilePath = $this->grepImageFileFromURL($imgPath);

            $img = new Imagick($tmpFilePath);
        
            $draw = new ImagickDraw();
            $draw->setStrokeColor($strokeColor);
            $draw->setFillColor($fillColor);
        
            $draw->setStrokeWidth(1);
            $draw->setFontSize($fontSize);
        
            $draw->setFont(plugin_dir_path(__FILE__)."/asset/font/font.ttf");
            $img->annotateimage($draw, $fontOffsetX, $fontOffsetY+$fontSize, 0, $text);
        
            $img->setImageFormat('png');
            return $img;
        }

        
        public function overlayImageEffect($bgImgPath, $topImgPath, $offsetX=0, $offsetY=0, $resizeTopWidth=0, $resizeTopHeight=0)
        {           
            $tmpBgImgPath = $this->grepImageFileFromURL($bgImgPath);

            $background = new Imagick($tmpBgImgPath);
            $top = null;

            if($resizeTopWidth==0 && $resizeTopHeight==0){
                $tmpTopImgPath = $this->grepImageFileFromURL($topImgPath);
                $top = new Imagick($tmpTopImgPath);

            }else{
                $top = $this->thumbnail($topImgPath, $resizeTopWidth, $resizeTopHeight);
            }

            $background->compositeImage($top, Imagick::COMPOSITE_ATOP, $offsetX, $offsetY);

            //Output the final image
            $background->setImageFormat('png');

            return $background;
        }


        public function alphaImage($imgPath, $opacityNum="0.47"){
            $tmpFilePath = $this->grepImageFileFromURL($imgPath);

            $img = new Imagick($tmpFilePath);
            $img->setImageOpacity($opacityNum);
            $img->setImageFormat('png');

            return $img;
        }


        public function compsiteImages($imgPath1, $imgPath2, $type=imagick::COMPOSITE_MULTIPLY){
            $img1FileName = $this->grepImageFileFromURL($imgPath1);
            $img1 = new Imagick($img1FileName);

            $img2FileName = $this->grepImageFileFromURL($imgPath2);
            $img2 = new Imagick($img2FileName);
            
            $img1->compositeImage($img2, $type, 0, 0);

            $img1->setImageFormat("png");

            return $img1;
        }


        public function imagickComposite($leftImgPath, $rightImgPath)
        {
            //Load the images
            $leftFileName = $this->grepImageFileFromURL($leftImgPath);
            $left = new Imagick($leftFileName);
            
            $rightFileName = $this->grepImageFileFromURL($rightImgPath);
            $right = new Imagick($rightFileName);

            $gradient = new Imagick(plugin_dir_path(__FILE__)."/asset/image/overlap_mask.png");

            // Get 2 images Min Size of width / Height
            $tmpMinHeight = min($left->getImageHeight(), $right->getImageHeight());
            $tmpMinWidth = min($left->getImageWidth(), $right->getImageWidth());

            $left->scaleImage($tmpMinWidth, $tmpMinHeight, true);
            $right->scaleImage($tmpMinWidth, $tmpMinHeight, true);

            $gradient->scaleImage( ($left->getImageWidth() + $right->getImageWidth()) *2/3, min($left->getImageHeight(), $right->getImageHeight()), true);
        
            //The right bit will be offset by a certain amount - avoid recalculating.
            $offsetX = $gradient->getImageWidth() - $right->getImageWidth();
        
        
            //Fade out the left part - need to negate the mask to
            //make math correct
            $gradient2 = clone $gradient;
            $gradient2->negateimage(false);
            $left->compositeimage($gradient2, Imagick::COMPOSITE_COPYOPACITY, 0, 0);
        
            //Fade out the right part
            $right->compositeimage($gradient, Imagick::COMPOSITE_COPYOPACITY, -$offsetX, 0);
        
            //Create a new canvas to render everything in to.
            $canvas = new Imagick();
            $canvas->newImage($gradient->getImageWidth(), $gradient->getImageHeight(), new ImagickPixel('black'));
        
            //Blend left half into final image
            $canvas->compositeimage($left, Imagick::COMPOSITE_BLEND, 0, 0);
        
            //Blend Right half into final image
            $canvas->compositeimage($right, Imagick::COMPOSITE_BLEND, $offsetX, 0);

            //Output the final image
            $canvas->setImageFormat('png');
        
            return $canvas;
        }


        public function returnImgUrlPath($imgObj){
            $filename = uniqid("tmp-").".png";
            $tmpFolder = $this->image_effects_settings["processed_image_folder"];

            file_put_contents($tmpFolder.$filename, $imgObj);

            $tmpPath = explode('wp-content', $tmpFolder);

            return "/wp-content".$tmpPath[1].$filename;
        }


        private function grepImageFileFromURL($imageFullURLPath){
            $remote_image = file_get_contents($imageFullURLPath);
            $tmpImgFileName = "tmp-".mktime().".jpg";
            file_put_contents($this->image_effects_settings["save_temp_file_dir"] .$tmpImgFileName, $remote_image);

            return $this->image_effects_settings["save_temp_file_dir"] .$tmpImgFileName;
        }
/*
        private function imageValueReturn($imgObj, $isSaveFile){
            if(trim($isSaveFile)=='y'){
                $filename = "tmp-".time().".png";

                $tmpFolder = $this->image_effects_settings["processed_image_folder"];

                file_put_contents($tmpFolder.$filename, $imgObj);

                $tmpPath = explode('wp-content', $tmpFolder);

                return "<img src='/wp-content".$tmpPath[1].$filename."' border='0'/>";
            }else{
                header("Content-Type: image/png");
                return $imgObj->getImageBlob();
            }
        }
*/
    }

    /**
	 * Main instance of Image_effects_Plugin.
	 *
	 * Returns the main instance of Image_effects_Plugin to prevent the need to use globals.
	 *
	 * @return Image_effects_Plugin
	 */

	function image_effects() {
		return Image_effects_Plugin::get_instance();
	}
}

add_action( 'plugins_loaded', 'image_effects', 10 );
register_activation_hook( __FILE__, array( 'Image_effects_Plugin', 'activate_plugin' ) );

?>