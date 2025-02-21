<?php
/**
 * Plugin Name: Simple WP Crossposting – Bricks Builder
 * Plugin URL: https://rudrastyh.com/support/bricks-builder
 * Description: Adds better compatibility with Bricks Builder.
 * Author: Misha Rudrastyh
 * Author URI: https://rudrastyh.com
 * Version: 1.1
 */

class Rudr_SWC_Bricks_Builder {

	function __construct() {
		add_filter( 'rudr_swc_pre_crosspost_meta', array( $this, 'process' ), 25, 4 );
	}

	public function process( $meta_value, $meta_key, $object_id, $blog ) {

		if( ! in_array(
			$meta_key,
			array(
				'_bricks_page_header_2',
				'_bricks_page_content_2',
				'_bricks_page_footer_2',
			)
		) ) {
			return $meta_value;
		}

		// now we convert the meta key json into an array of elements
		$bricks = maybe_unserialize( $meta_value );

		foreach( $bricks as &$brick ) {

			switch( $brick[ 'name' ] ) {
				case 'image' : {
					if( ! empty( $brick[ 'settings' ][ 'image' ] ) ) {
						$brick[ 'settings' ][ 'image' ] = $this->process_image_in_brick( $brick[ 'settings' ][ 'image' ], $blog );
					}
					break;
				}
				case 'image-gallery':
				case 'carousel': {
					if( ! empty( $brick[ 'settings' ][ 'items' ][ 'images' ] ) ) {
						foreach( $brick[ 'settings' ][ 'items' ][ 'images' ] as &$image ) {
							$image = $this->process_image_in_brick( $image, $blog );
						}
					}
					break;
				}
				default : {
					// processing background
					if( ! empty( $brick[ 'settings' ][ '_background' ][ 'image' ] ) ) {
						$brick[ 'settings' ][ '_background' ][ 'image' ] =  $this->process_image_in_brick( $brick[ 'settings' ][ '_background' ][ 'image' ], $blog );
					}
					break;
				}
			}

		}
		// file_put_contents( __DIR__ . '/log.txt', print_r( $bricks, true ) );
		return $bricks;

	}


	private function process_image_in_brick( $image, $blog ){
		if( empty( $image[ 'id' ] ) ) {
			return $image;
		}

		$upload = Rudr_Simple_WP_Crosspost::maybe_crosspost_image( $image[ 'id' ], $blog );
		if( $upload ) {
			$image[ 'id' ] = $upload[ 'id' ];
			$image[ 'full' ] = $upload[ 'url' ];
			$image[ 'url' ] = $upload[ 'url' ];
		}
		return $image;

	}

}

new Rudr_SWC_Bricks_Builder();
