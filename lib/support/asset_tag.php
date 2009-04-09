<?php
	require_once(dirname(__FILE__) . '/base.php');
	class AssetTag extends TagHelper {
	
	
		public static function stylesheet_link_tag() {
			$args = func_get_args();
			$style_sheet_path = Nimble::getInstance()->config['stylesheet_folder'];
			$style_sheet_base_url = Nimble::getInstance()->config['stylesheet_folder_url'];
			
			(string) $out = '';
			foreach($args as $css) {
					if(!preg_match("/\.css$/" , $css)) {
						$css = $css . '.css';
					}
					$url = self::compute_public_path($css, $style_sheet_path, $style_sheet_base_url);
					$out .= self::stylesheet_tag($url) . "\n";
			}
			return $out;
		}
		public static function javascript_include_tag() {
			$args = func_get_args();
			$javascript_path = Nimble::getInstance()->config['javascript_folder'];
			$javascript_base_url = Nimble::getInstance()->config['javascript_folder_url'];
			
			(string) $out = '';
			foreach($args as $js) {
					if(!preg_match("/\.js$/" , $js)) {
						$js = $js . '.js';
					}
					$url = self::compute_public_path($js, $javascript_path, $javascript_base_url);
					$out .= self::javascript_tag($url) . "\n";
			}
			return $out;
		}
		
		
		public static function stylesheet_tag($url, $media='screen') {
			return self::tag('link', array('rel' => 'stylesheet', 'type' => Mime::CSS, 'media' => $media, 'href' => htmlspecialchars($url)));
		}
		
		public static function javascript_tag($url) {
			return self::content_tag('script', '', array('type' => Mime::JS, 'src' => $url));
		}
		
		private static function asset_id($source, $dir) {
			$key = $source . '-mtime';
			$path = FileUtils::join();
			if(StringCacher::isCached($key)) {
				return StringCacher::fetch($key);
			}else{
				return StringCacher::set($key, filemtime(FileUtils::join($dir, $source)));
			}
		}
		
		private static function rewrite_asset_path($source, $dir) {
			$asset_id = self::asset_id($source, $dir);
			if(empty($asset_id)) {
				return $source;
			}else{
				return $source . '?' . $asset_id;
			}
		}
		
		
		private static function compute_public_path($source, $dir, $url) {
			if(!preg_match('{^[-a-z]+://}', $source)) {
				return $url . '/' . self::rewrite_asset_path($source, $dir);
			}else{
				return $source;
			}
		
		}
		
		
	}
	


?>