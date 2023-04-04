<?php
namespace Mahbub500\Plugin;

/**
 * if accessed directly, exit.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Plugin
 * @subpackage Base
 * @author Mahbub <mahbubmr500@gmail.com>
 */
abstract class Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}	
	
	/**
	 * @see add_action
	 */
	public function action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
		add_action( $tag, [ $this, $callback ], $priority, $accepted_args );
	}

	/**
	 * @see add_filter
	 */
	public function filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
		add_filter( $tag, [ $this, $callback ], $priority, $accepted_args );
	}

	/**
	 * @see add_shortcode
	 */
	public function register( $tag, $callback ) {
		add_shortcode( $tag, [ $this, $callback ] );
	}

	/**
	 * @see add_action( 'wp_ajax_..' )
	 */
	public function priv( $handle, $callback ) {
		$this->action( "wp_ajax_{$handle}", $callback );
	}

	/**
	 * @see add_action( 'wp_ajax_nopriv_..' )
	 */
	public function nopriv( $handle, $callback ) {
		$this->action( "wp_ajax_nopriv_{$handle}", $callback );
	}

	/**
	 * @see add_action( 'wp_ajax_..' )
	 * @see add_action( 'wp_ajax_nopriv_..' )
	 */
	public function all( $handle, $callback ) {
		$this->priv( $handle, $callback );
		$this->nopriv( $handle, $callback );
	}

	/**
	 * Sanitize data
	 * 
	 * @param mix $input The input
	 * @param string $type The data type
	 * 
	 * @return mix
	 */
	public function sanitize( $input, $type = 'text' ) {

		if( 'array' == $type ) {
			$sanitized = [];

			foreach ( $input as $key => $value ) {
				if( is_array( $value ) ) {
					$sanitized[ $key ] = $this->sanitize( $value, $type );
				}
				else {
					// identify textarea to fix possible linebreak issues
					$_type = count( explode( PHP_EOL, $value ) ) > 1 ? 'textarea' : 'text';

					$sanitized[ $key ] = $this->sanitize( $value, $_type );
				}
			}

			return $sanitized;
		}

		if( ! in_array( $type, [ 'textarea', 'email', 'file', 'class', 'key', 'title', 'user', 'option', 'meta' ] ) ) {
			$type = 'text';
		}

		if( array_key_exists( $type,
			$maps = [
				'text'      => 'text_field',
				'textarea'  => 'textarea_field',
				'file'      => 'file_name',
				'class'     => 'html_class',
			]
		) ) {
			$type = $maps[ $type ];
		}

		$fn = "sanitize_{$type}";

		return $fn( $input );
	}
}