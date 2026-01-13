<?php

class Mandrill_Exception extends Exception {}

class Mandrill {
    const API_VERSION = '1.0';    
    const END_POINT = 'https://mandrillapp.com/api/';
    
    var $api;
    var $output;
    
    // PHP 5.0
    function __construct($api) {
        if ( empty($api) ) throw new Mandrill_Exception(esc_html__('Invalid API key', 'wpmandrill') );
        try {
        
            $response = $this->request('users/ping2', array( 'key' => $api ) );        
            if ( !isset($response['PING']) || $response['PING'] != 'PONG!' ) throw new Mandrill_Exception(esc_html__('Invalid API key', 'wpmandrill'));
            
            $this->api = $api;
            
        } catch ( Exception $e ) {
            throw new Mandrill_Exception(esc_html($e->getMessage()));
        }
    }
    
    // PHP 4.0
    function Mandrill($api) { $this->__construct($api); }
    
    /**
	 * Work horse. Every API call use this function to actually make the request to Mandrill's servers.
	 *
	 * @link https://mandrillapp.com/api/docs/
	 *
	 * @param string $method API method name
	 * @param array $args query arguments
	 * @param string $http GET or POST request type
	 * @param string $output API response format (json,php,xml,yaml). json and xml are decoded into arrays automatically.
	 * @return array|string|Mandrill_Exception
	 */
	function request($method, $args = array(), $http = 'POST', $output = 'json') {
		if( !isset($args['key']) )
			$args['key'] = $this->api;

        $this->output = $output;
        
		$api_version = self::API_VERSION;
		$dot_output = ('json' == $output) ? '' : ".{$output}";

		$url = self::END_POINT . "{$api_version}/{$method}{$dot_output}";

		switch ($http) {

			case 'GET':
                //some distribs change arg sep to &amp; by default
                $sep_changed = false;
                if (ini_get("arg_separator.output")!="&"){
                    $sep_changed = true;
                    $orig_sep = ini_get("arg_separator.output");
                    ini_set("arg_separator.output", "&");
                }

				$url .= '?' . http_build_query($args);
				
                if ($sep_changed){
                    ini_set("arg_separator.output", $orig_sep);
                }
                
				$response = $this->http_request($url, array(),'GET');
				break;

			case 'POST':
				$response = $this->http_request($url, $args, 'POST');
				break;

			default:
				throw new Mandrill_Exception(esc_html__('Unknown request type', 'wpmandrill'));
		}

		$response_code  = $response['header']['http_code'];
		$body           = $response['body'];

		switch ($output) {
			
			case 'json':

				$body = json_decode($body, true);
				break;

			case 'php':

				$body = unserialize($body);
				break;
		}		

		if( 200 == $response_code ) {
			return $body;
		} else {
			$code = isset($body['code']) ? esc_html($body['code']) : esc_html__('Unknown', 'wpmandrill');
			$message = isset($body['message']) ? esc_html($body['message']) : esc_html__('Unknown', 'wpmandrill');

			$response_code = esc_html($response_code);

			error_log("wpMandrill Error: Error {$code}: {$message}");

			// esc_html here is redundant but it's here to satisfy the WordPress plugin security checker
			throw new Mandrill_Exception( esc_html("wpMandrill Error: {$code}: {$message}"), esc_html($response_code));
		}
	}

	/**
	 * @link https://mandrillapp.com/api/docs/users.html#method=ping
	 *
	 * @return array|Mandrill_Exception
	 */
	function users_ping() {

		return $this->request('users/ping');
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/users.html#method=info
	 *
	 * @return array|Mandrill_Exception
	 */
	function users_info() {

		return $this->request('users/info');
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/users.html#method=senders
	 *
	 * @return array|Mandrill_Exception
	 */
	function users_senders() {

		return $this->request('users/senders');
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/users.html#method=disable-sender
	 *
	 * @return array|Mandrill_Exception
	 */
	function users_disable_sender($domain) {

		return $this->request('users/disable-senders', array('domain' => $domain) );
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/users.html#method=verify-sender
	 *
	 * @return array|Mandrill_Exception
	 */
	function users_verify_sender($email) {

		return $this->request('users/verify-senders', array('domain' => $email) );
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/senders.html#method=domains
	 *
	 * @return array|Mandrill_Exception
	 */
	function senders_domains() {

		return $this->request('senders/domains');
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/senders.html#method=list
	 *
	 * @return array|Mandrill_Exception
	 */
	function senders_list() {

		return $this->request('senders/list');
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/senders.html#method=info
	 *
	 * @return array|Mandrill_Exception
	 */
	function senders_info($email) {

		return $this->request('senders/info', array( 'address' => $email) );
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/senders.html#method=time-series
	 *
	 * @return array|Mandrill_Exception
	 */
	function senders_time_series($email) {

		return $this->request('senders/time-series', array( 'address' => $email) );
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/tags.html#method=list
	 *
	 * @return array|Mandrill_Exception
	 */
	function tags_list() {

		return $this->request('tags/list');
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/tags.html#method=info
	 *
	 * @return array|Mandrill_Exception
	 */
	function tags_info($tag) {

		return $this->request('tags/info', array( 'tag' => $tag) );
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/tags.html#method=time-series
	 *
	 * @return array|Mandrill_Exception
	 */
	function tags_time_series($tag) {

		return $this->request('tags/time-series', array( 'tag' => $tag) );
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/tags.html#method=all-time-series
	 *
	 * @return array|Mandrill_Exception
	 */
	function tags_all_time_series() {

		return $this->request('tags/all-time-series');
	}
	
	/**
	 * @link https://mandrillapp.com/api/docs/templates.html#method=add
	 *
	 * @return array|Mandrill_Exception
	 */
	function templates_add($name, $code) {

		return $this->request('templates/add', array('name' => $name, 'code' => $code) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/templates.html#method=update
	 *
	 * @return array|Mandrill_Exception
	 */
	function templates_update($name, $code) {

		return $this->request('templates/update', array('name' => $name, 'code' => $code) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/templates.html#method=delete
	 *
	 * @return array|Mandrill_Exception
	 */
	function templates_delete($name) {

		return $this->request('templates/delete', array('name' => $name) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/templates.html#method=info
	 *
	 * @return array|Mandrill_Exception
	 */
	function templates_info($name) {

		return $this->request('templates/info', array('name' => $name) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/templates.html#method=list
	 *
	 * @return array|Mandrill_Exception
	 */
	function templates_list() {

		return $this->request('templates/list');
	}

	/**
	 * @link https://mandrillapp.com/api/docs/templates.html#method=time-series
	 *
	 * @return array|Mandrill_Exception
	 */
	function templates_time_series($name) {

		return $this->request('templates/time-series', array('name' => $name) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/webhooks.html#method=add
	 *
	 * @return array|Mandrill_Exception
	 */
	function webhooks_add($url, $events) {

		return $this->request('webhooks/add', array('url' => $url, 'events' => $events) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/webhooks.html#method=update
	 *
	 * @return array|Mandrill_Exception
	 */
	function webhooks_update($url, $events) {

		return $this->request('webhooks/update', array('url' => $url, 'events' => $events) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/webhooks.html#method=delete
	 *
	 * @return array|Mandrill_Exception
	 */
	function webhooks_delete($id) {

		return $this->request('webhooks/delete', array('id' => $id) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/webhooks.html#method=info
	 *
	 * @return array|Mandrill_Exception
	 */
	function webhooks_info($id) {

		return $this->request('webhooks/info', array('id' => $id) );
	}

	/**
	 * @link https://mandrillapp.com/api/docs/webhooks.html#method=list
	 *
	 * @return array|Mandrill_Exception
	 */
	function webhooks_list() {
		return $this->request('webhooks/list');
	}

	/**
	 * @link https://mandrillapp.com/api/docs/messages.html#method=search
	 *
	 * @return array|Mandrill_Exception
	 */
	function messages_search($query, $date_from = '', $date_to = '', $tags = array(), $senders = array(), $limit = 100) {
		return $this->request('messages/search', compact('query', 'date_from', 'date_to', 'tags', 'senders', 'limit'));
	}

	/**
	 * @link https://mandrillapp.com/api/docs/messages.html#method=send
	 *
	 * @return array|Mandrill_Exception
	 */
	function messages_send($message) {
		$async = $message['async'];
		$ip_pool = $message['ip_pool'];
		$send_at = $message['send_at'];
		
		return $this->request('messages/send', array('message' => $message, 'async' => $async, 'ip_pool' => $ip_pool, 'send_at' => $send_at) );
	}
 
	/**
	 * @link https://mandrillapp.com/api/docs/messages.html#method=send-template
	 *
	 * @return array|Mandrill_Exception
	 */
	function messages_send_template($template_name, $template_content, $message) {
		$async = $message['async'];
		$ip_pool = $message['ip_pool'];
		$send_at = $message['send_at'];
		
		return $this->request('messages/send-template', compact('template_name', 'template_content','message', 'async', 'ip_pool', 'send_at') );
	}

	function http_request($url, $fields = array(), $method = 'POST') {
		if (!in_array($method, array('POST', 'GET'))) {
			$method = 'POST';
		}
		if (!isset($fields['key'])) {
			$fields['key'] = $this->api;
		}

		$fields = is_array($fields) ? http_build_query($fields) : $fields;
		$useragent = wpMandrill::getUserAgent();

		// Set up the arguments for the request
		$args = array(
			'method'      => $method,
			'body'        => $fields,
			'timeout'     => 120, // 2 minutes timeout
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
				'User-Agent' => $useragent,
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Expect' => ''
			),
			'sslverify'   => false // this corresponds to CURLOPT_SSL_VERIFYPEER = 0
		);

		try {
			// Make the request using wp_remote_request()
			$response = wp_remote_request($url, $args);

			// Error handling for wp errors
			if (is_wp_error($response)) {
				$error = $response->get_error_message();

				// Otherwise, throw the generic Mandrill_Exception with error details
				throw new Mandrill_Exception(esc_html($error), 500);
			}

			// Retrieve the response code and body
			$http_code = wp_remote_retrieve_response_code($response);
			$response_body = wp_remote_retrieve_body($response);

			// Check if the body is empty and if the HTTP code indicates success
			if ($http_code == 200 && empty($response_body)) {
				throw new Mandrill_Exception('Empty reply from server', 52);
			}

			$info = array('http_code' => $http_code);
			return array('header' => $info, 'body' => $response_body, 'error' => '');

		} catch (Mandrill_Exception $e) {
			// Handle the exception gracefully
			// Log the error or handle it appropriately
			error_log('Mandrill API error: ' . $e->getMessage());

			// Return a non-fatal response
			return array(
				'header' => array('http_code' => $e->getCode()),
				'body' => '',
				'error' => $e->getMessage()
			);
		}
	}

	static function getAttachmentStruct($path) {
		global $wp_filesystem;

		$struct = array();

		try {
			// Check if the path is a URL or a local file
			if ( filter_var( $path, FILTER_VALIDATE_URL ) ) {
				// Remote URL, use wp_remote_get()
				$response = wp_remote_get( $path );
				if ( is_wp_error( $response ) ) {
					throw new Exception('Error fetching the remote file: ' . $response->get_error_message());
				}
				$file_buffer = wp_remote_retrieve_body( $response );
			} else {
				// Local file, use WP_Filesystem
				if ( ! $wp_filesystem ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
					WP_Filesystem();
				}

				if ( ! $wp_filesystem->is_file( $path ) ) {
					throw new Exception( esc_html( $path ) . ' is not a valid file.' );
				}

				$file_buffer = $wp_filesystem->get_contents($path);
				if ( false === $file_buffer ) {
					throw new Exception('Error reading file: ' . esc_html($path));
				}
			}

			$file_buffer = chunk_split(base64_encode($file_buffer), 76, "\n");

			$filename = basename($path);
			$mime_type = '';

			if ( function_exists('finfo_open') && function_exists('finfo_file') ) {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime_type = finfo_file($finfo, $path);
			} elseif ( function_exists('mime_content_type') ) {
				$mime_type = mime_content_type($path);
			}

			if ( !empty($mime_type) ) {
				$struct['type'] = $mime_type;
			}
			$struct['name'] = esc_html($filename);
			$struct['content'] = $file_buffer;

		} catch (Exception $e) {
			throw new Mandrill_Exception('Error creating the attachment structure: ' . esc_html($e->getMessage()));
		}

		return $struct;
	}

	static function isValidContentType($ct) {
        // Now Mandrill accepts any content type. 
        return true;
    }
    
    static function getValidContentTypes() {
        return array(
                  'image/',
                  'text/',
                  'application/pdf',
        		  'audio/',
        		  'video/'
              );
    }
}

?>
