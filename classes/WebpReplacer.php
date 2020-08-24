<?php

namespace webp\classes;

use DOMWrap\Document;
use DOMWrap\Element;
use DOMWrap\NodeList;

/**
 * Class WebpReplacer Replaces "on fly" urls to jpg/png images into webp. This class is singleton.
 * Usage example:
 *
 * ```
 * $webpReplacer  = WebpReplacer::getInstance();
 * add_filter( 'the_content', [ $webpReplacer, 'replace' ] );
 * ```
 */
class WebpReplacer {
	private static $instance;

	/**
	 * WebpReplacer constructor.
	 */
	private function __construct() {
	}

	public static function getInstance(): WebpReplacer {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Replaces "on fly" urls to jpg/png images into webp.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function replace( string $content ): string {
		$doc = new Document();
		$doc->html( $content );
		$images = $doc->find( 'img' );

		foreach ( $images as $imageNode ) {
			/* @var Element $imageNode */
			$pictureDocument = $this->buildFallbackPictureDocument( $doc, $imageNode );
			$imageNode->replaceWith( $pictureDocument );
		}

		$newContent = $doc->html();

		return $newContent;
	}

	/**
	 * Builds picture tag fallback to show image proper in browsers which don't support webp.
	 * Returned picture tag looks like this:
	 *
	 * ```
	 * <picture>
	 *      <source srcset="img/awesomeWebPImage.webp" type="image/webp">
	 *      <source srcset="img/creakyOldJPEG.jpg" type="image/jpeg">
	 *      <img src="img/creakyOldJPEG.jpg" alt="Alt Text!">
	 * </picture>
	 * ```
	 *
	 * @param Document $document
	 * @param Element $imageNode
	 *
	 * @return NodeList
	 */
	private function buildFallbackPictureDocument( Document $document, Element $imageNode ): NodeList {
		$srcset         = $this->getSrcSet( $imageNode );
		$replacedSrcset = str_replace( [ '.jpg', '.jpeg', '.png' ], [
			'.jpg.webp',
			'.jpeg.webp',
			'.png.webp'
		], $srcset );

		$pictureDocument = $document->create( '<picture></picture>' );

		$webpSource = $document->create( '<source></source>' );
		$webpSource->setAttr( 'type', 'image/webp' );
		$webpSource->setAttr( 'srcset', $replacedSrcset );

		$regularSource = $document->create( '<source></source>' );
		$imageType     = $this->getImageType( $imageNode );
		$regularSource->setAttr( 'type', $imageType );
		$regularSource->setAttr( 'srcset', $srcset );

		$pictureDocument->append( $webpSource );
		$pictureDocument->append( $regularSource );
		$pictureDocument->append( $imageNode );

		return $pictureDocument;
	}

	private function getSrcSet( Element $imageNode ): string {
		$src    = $imageNode->getAttr( 'src' );
		$srcset = $imageNode->getAttr( 'srcset' );

		$srcsetToReturn = ( ! empty( $srcset ) ) ? $srcset : $src;

		return $srcsetToReturn;
	}

	private function getImageType( Element $imageNode ): string {
		$src          = $imageNode->getAttr( 'src' );
		$imgExtension = pathinfo( $src, PATHINFO_EXTENSION );

		return 'image/' . $imgExtension;
	}
}
