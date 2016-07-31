<?php

	class Lib_cURL extends Lib_base {
		
		private $cURL = null, $url = null, $method = null, $content_return = null, $redirect = null, $headers = array();
		
		private function __clone() {}
		
		function __construct() {}
		
		/**
		 * Initializes cURL library.
		 * 
		 * @param string $url
		 * <p>The URL to call.</p>
		 * 
		 * @param boolean $verify_ssl [optional]
		 * <p>Toggle SSL verification.</p>
		 */
		public function initCURL($url, $verify_ssl = false) {
			$this->cURL = curl_init();
			
			$this->url = $url;
			curl_setopt($this->cURL, CURLOPT_SSL_VERIFYPEER, (int)$verify_ssl);
			curl_setopt($this->cURL, CURLOPT_SSL_VERIFYHOST, (int)$verify_ssl);
			curl_setopt($this->cURL, CURLOPT_FRESH_CONNECT, true);
		}
		
		/**
		 * Sets the cURL request method.
		 * 
		 * @param string $method
		 * <p>Request method.</p>
		 * <p>( E.g. <b>'get', 'post', 'put', 'patch', 'delete'</b> )</p>
		 * 
		 * @param mixed $post_fields [optional]
		 * <p>The post fields, only required when <i>$method</i> is set to post.</p>
		 * 
		 * @param string $post_type [optional]
		 * <p>Data type of post fields.</p>
		 * <p>( E.g. <b>'form', 'xml', 'json'</b> )</p>
		 * 
		 * @return boolean Return <b>FALSE</b> if cURL is not set.
		 */
		public function setMethod($method = "get", $post_fields = null, $post_type = "form") {
			if($this->cURL === null || $this->cURL === false) {
				return false;
			}
			
			$method = in_array(strtolower($method), array("get", "post", "put", "patch", "delete")) ? strtolower($method) : "get";
			
			if($method === "get") {
				curl_setopt($this->cURL, CURLOPT_HTTPGET, true);
				curl_setopt($this->cURL, CURLOPT_POST, false);
			} else {
				if($post_fields !== null) {
					if($post_type === "form") {
						if(is_array($post_fields) === true) {
							$post_fields = http_build_query($post_fields, '', '&');
						}

						$this->headers[] = "Content-Type: application/x-www-form-urlencoded";
					} elseif($post_type === "json") {
						if(is_array($post_fields) === true) {
							$post_fields = json_encode($post_fields);
						}

						$this->headers[] = "Content-Type: application/json";
					} elseif($post_type === "xml") {
						$this->headers[] = "Content-Type: text/xml";
					}

					curl_setopt($this->cURL, CURLOPT_POSTFIELDS, $post_fields);

					$this->headers[] = 'Content-Length: ' . strlen($post_fields);
				}
				
				if($method === "post") {
					curl_setopt($this->cURL, CURLOPT_POST, true);
				} elseif($method === "put" || $method === "patch" || $method === "delete") {
					curl_setopt($this->cURL, CURLOPT_CUSTOMREQUEST, strtoupper($method));
				}
			}
			
			$this->method = true;
		}
		
		/**
		 * Sets the authentication type of the request.
		 * 
		 * @param string $type
		 * <p>The authentication type.</p>
		 * <p>( E.g. <b>'apikey', 'basic', 'bearer'</b> )</p>
		 * 
		 * @param string $username [optional]
		 * <p>The authentication username.</p>
		 * 
		 * @param string $password [optional]
		 * <p>The authentication password.</p>
		 * 
		 * @return boolean Return <b>FALSE</b> if cURL is not set.
		 */
		public function setAuth($type = "basic", $username = null, $password = null) {
			if($this->cURL === null || $this->cURL === false) {
				return false;
			}
			
			if($type === "apikey") {
				$this->url = $this->url.(strpos($this->url, "?") === false ? "?" : "&").$username. '=' .$password;
			} elseif($type === "basic") {
				curl_setopt($this->cURL, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
				curl_setopt($this->cURL, CURLOPT_USERPWD, $username. ':' .$password);
			} elseif($type === "bearer") {
				curl_setopt($this->cURL, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
				$this->headers[] = 'Authorization: Bearer ' .$password;
			}
		}
		
		/**
		 * Sets the content return options of the request.
		 * 
		 * @param boolean $return
		 * <p>Toggle if request should expect return content.</p>
		 * 
		 * @param string $content_return [optional]
		 * <p>Type of content to expect.</p>
		 * <p>( E.g. <b>'json', 'image', 'text', 'xml'</b> )</p>
		 * 
		 * @return boolean Return <b>FALSE</b> if cURL is not set.
		 */
		public function setContentReturn($return = true, $content_return = "text") {
			if($this->cURL === null || $this->cURL === false) {
				return false;
			}
			
			if($return === true) {
				$content_return = strtolower($content_return);
				
				switch($content_return) {
					case "json":
						$this->headers[] = "Accept: application/json";
						break;
					case "image":
						$this->headers[] = "Accept: image/png,image/jpeg,image/gif";
						break;
					case "text":
					case "html":
					case "xml":
					default:
						$this->headers[] = "Accept: text/xml,application/xml,application/xhtml+xml,text/html,text/plain";
						break;
				}
				
				curl_setopt($this->cURL, CURLOPT_RETURNTRANSFER, true);
				
				$this->content_return = $content_return;
			} elseif($return === false) {
				curl_setopt($this->cURL, CURLOPT_RETURNTRANSFER, false);
				
				$this->content_return = false;
			}
		}
		
		/**
		 * Sets the redirect and follow options of the request.
		 * 
		 * @param boolean|int $redirect [optional]
		 * <p>Toggle if request should follow redirects, set to <b>FALSE</b> to ignore redirects.</p>
		 * 
		 * @return boolean Return <b>FALSE</b> if cURL is not set.
		 */
		public function setRedirect($redirect = 2) {
			if($this->cURL === null || $this->cURL === false) {
				return false;
			}
			
			if($redirect !== false) {
				curl_setopt($this->cURL, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($this->cURL, CURLOPT_MAXREDIRS, (int)$redirect);
			} else {
				curl_setopt($this->cURL, CURLOPT_FOLLOWLOCATION, false);
				curl_setopt($this->cURL, CURLOPT_MAXREDIRS, 0);
			}
			
			$this->redirect = true;
		}
		
		/**
		 * Executes the cURL request.
		 * 
		 * @param boolean $debug [optional]
		 * <p>Toggle return of debug data from request.</p>
		 * 
		 * @return boolean|mixed Return <b>FALSE</b> if cURL is not set, the response data if successful.
		 */
		public function execCURL($debug = false) {
			if($this->cURL === null || $this->cURL === false) {
				return false;
			}
			
			if($this->method === null) {
				$this->setMethod();
			}
			
			if($this->content_return === null) {
				$this->setContentReturn();
			}
			
			if($this->redirect === null) {
				$this->setRedirect();
			}
			
			curl_setopt($this->cURL, CURLOPT_URL, $this->url);
			
			if(! empty($this->headers)) {
				curl_setopt($this->cURL, CURLOPT_HTTPHEADER, $this->headers);
			}
			
			curl_setopt($this->cURL, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($this->cURL, CURLOPT_TIMEOUT, 30);
			
			if($debug === true) {
				curl_setopt($this->cURL, CURLOPT_HEADER, true);
				curl_setopt($this->cURL, CURLINFO_HEADER_OUT, true);
			} else {
				curl_setopt($this->cURL, CURLOPT_HEADER, false);
			}
			
			$cURL_response = curl_exec($this->cURL);
			$response = null;
			
			if($debug === true || ($cURL_response === false || (bool)curl_errno($this->cURL) !== false)) {
				$response = array("info" => curl_getinfo($this->cURL), "errno" => curl_errno($this->cURL), "error" => curl_error($this->cURL));
				
				if($cURL_response !== false) {
					list($response_header, $cURL_response) = explode("\r\n\r\n", $cURL_response);
					$response["info"]["response_header"] = $response_header;
				}
			}
			
			curl_close($this->cURL);
			
			if($cURL_response !== false && $this->content_return !== null) {
				if($this->content_return === "json") {
					$cURL_response = json_decode($cURL_response, true);
				}
			}
			
			$response["response"] = $cURL_response;
			
			$this->clearCURL();
			
			return $response;
		}
		
		/**
		 * Clears all cURL properties.
		 */
		private function clearCURL() {
			$this->cURL = null;
			$this->url = null;
			$this->method = null;
			$this->content_return = null;
			$this->redirect = null;
			$this->headers = array();
		}
	}

?>