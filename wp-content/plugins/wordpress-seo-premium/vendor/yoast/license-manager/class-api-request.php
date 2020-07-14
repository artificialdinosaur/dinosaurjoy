<?php

if ( ! class_exists( "Yoast_API_Request", false ) ) {

	/**
	* Handles requests to the Yoast EDD API
	*/
	class Yoast_API_Request {

		/**
		* @var string Request URL
		*/
		private $url = '';

		/**
		* @var array Request parameters
		*/
		private $args = array(
			'method' => 'GET',
			'timeout' => 10,
			'sslverify' => false,
			'headers' => array(
				'Accept-Encoding' => '*',
				'X-Yoast-EDD' => '1'
			)
		);

		/**
		* @var boolean
		*/
		private $success = false;

		/**
		* @var mixed
		*/
		private $response;

		/**
		* @var string
		*/
		private $error_message = '';

		/**
		* Constructor
		* 
		* @param string url
		* @param array $args
		*/
		public function __construct( $url, array $args = array() ) {

			// 更多精品WP资源尽在喵容：miaoroom.com
//set api url
			$this->url = $url;

			// 更多精品WP资源尽在喵容：miaoroom.com
//set request args (merge with defaults)
			$this->args = wp_parse_args( $args, $this->args );

			// 更多精品WP资源尽在喵容：miaoroom.com
//fire the request
			$this->success = $this->fire();
		}

		/**
		* Fires the request, automatically called from constructor
		*
		* @return boolean
		*/
		private function fire() {

			// 更多精品WP资源尽在喵容：miaoroom.com
//fire request to shop
			$response = wp_remote_request( $this->url, $this->args );

			// 更多精品WP资源尽在喵容：miaoroom.com
//validate raw response
			if( $this->validate_raw_response( $response ) === false ) {
				return false;
			}

			// 更多精品WP资源尽在喵容：miaoroom.com
//decode the response
			$this->response = json_decode( wp_remote_retrieve_body( $response ) );

			// 更多精品WP资源尽在喵容：miaoroom.com
//response should be an object
			if( ! is_object( $this->response ) ) {
				$this->error_message = 'No JSON object was returned.';
				return false;
			}

			return true;
		}

		/**
		* @param object $response
		* @return boolean
		*/
		private function validate_raw_response( $response ) {

			// 更多精品WP资源尽在喵容：miaoroom.com
//make sure response came back okay
			if( is_wp_error( $response ) ) {
				$this->error_message = $response->get_error_message();
				return false;
			}

			// 更多精品WP资源尽在喵容：miaoroom.com
//check response code, should be 200
			$response_code = wp_remote_retrieve_response_code( $response );

			if( false === strstr( $response_code, '200' ) ) {

				$response_message = wp_remote_retrieve_response_message( $response );
				$this->error_message = "{$response_code} {$response_message}";

				return false;
			}

			return true;
		}

		/**
		* Was a valid response returned?
		*
		* @return boolean
		*/ 
		public function is_valid() {
			return ( $this->success === true );
		}

		/**
		* @return string
		*/
		public function get_error_message() {
			return $this->error_message;
		}

		/**
		* @return object
		*/
		public function get_response() {
			return $this->response;
		}
	}

}
