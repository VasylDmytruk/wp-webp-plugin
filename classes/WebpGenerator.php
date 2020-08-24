<?php

namespace webp\classes;

use webp\classes\helpers\StringHelper;

/**
 * Class WebpGenerator Generates (and saves) webp image, if '.webp' image not found. This class is singleton.
 * Usage example:
 *
 * ```
 * $webpGenerator = WebpGenerator::getInstance();
 * add_action( 'template_redirect', [ $webpGenerator, 'generateIfNeed' ] );
 * ```
 */
class WebpGenerator {
	private static $instance;

	/**
	 * @var int Webp image quality.
	 */
	private $imageQuality = 100;

	private function __construct() {
	}

	public static function getInstance(): WebpGenerator {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param int $imageQuality
	 */
	public function setImageQuality( int $imageQuality ): void {
		$this->imageQuality = $imageQuality;
	}

	/**
	 * Generates (and saves) webp image, if '.webp' image not found.
	 * After generate webp, redirects to new generated image url or old image url, if can not generate webp.
	 */
	public function generateIfNeed(): void {
		if ( is_404() ) {
			global $wp;
			$currentUrl = home_url( add_query_arg( [], $wp->request ) );

			if ( StringHelper::endsWith( $currentUrl, '.webp' ) ) {
				$imageUrl = $this->generateWebp( $currentUrl );
				wp_redirect( $imageUrl );
				die;
			}
		}
	}

	private function generateWebp( string $currentUrl ): string {
		$srcUrl  = rtrim( $currentUrl, '.webp' );
		$webpUrl = $currentUrl;

		$siteUrl  = get_site_url() . '/';
		$basePath = $this->getBasePath();

		$srcPath  = str_replace( $siteUrl, $basePath, $srcUrl );
		$webpPath = str_replace( $siteUrl, $basePath, $webpUrl );

		$imageResource     = $this->getImageResource( $srcPath );
		$generatedAndSaved = ( $imageResource )
			? $this->saveWebpToFile( $imageResource, $webpPath, $this->imageQuality )
			: false;

		$urlToReturn = ( $generatedAndSaved ) ? $webpUrl : $srcUrl;

		return $urlToReturn;
	}

	private function getBasePath(): string {
		if ( ! function_exists( 'get_home_path' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$basePath = get_home_path();

		return $basePath;
	}

	private function getImageResource( string $srcPath ) {
		$imageResource = null;

		$srcExtension = pathinfo( $srcPath, PATHINFO_EXTENSION );

		if ( $srcExtension === 'jpeg' || $srcExtension === 'jpg' ) {
			$imageResource = imagecreatefromjpeg( $srcPath );
		} elseif ( $srcExtension === 'png' ) {
			$imageResource = imagecreatefrompng( $srcPath );
		} else {
			// TODO add some warning log
		}

		return $imageResource;
	}

	private function saveWebpToFile( $imageResource, $webpPath, $quality ): bool {
		$saved = imagewebp( $imageResource, $webpPath, $quality );

		return $saved;
	}
}
