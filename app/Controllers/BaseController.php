<?php
namespace App\Controllers;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 *
 * @package CodeIgniter
 */

use CodeIgniter\Controller;

class BaseController extends Controller
{

	/**
	 * An array of helpers to be loaded automatically upon
	 * class instantiation. These helpers will be available
	 * to all other controllers that extend BaseController.
	 *
	 * @var array
	 */
	protected $helpers = [];

	/**
	 * Constructor.
	 */
	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		//--------------------------------------------------------------------
		// Preload any models, libraries, etc, here.
		//--------------------------------------------------------------------
		// E.g.:
		// $this->session = \Config\Services::session();
		helper('text');
		$incomingRequest = $this->request;
		$ip = $incomingRequest->getIPAddress();
		$method = $incomingRequest->getMethod(TRUE);
		$uri = $incomingRequest->uri;
		$host = $uri->getHost();
		$query_string = $uri->getQuery(); // ToDo: use parse_str() to decode query string
		$path = urldecode($uri->getPath());
		$auth = $uri->getAuthority();
		$user = $uri->getUserInfo();
		$requestId = random_string();
		$server_uri = $incomingRequest->getServer('REQUEST_URI');
		log_message('info', "[Request $requestId]"
			." IP: $ip, method: $method, query string: $query_string"
			.", path: $path, host: $host, server_uri: $server_uri");
		//log_message('info', "[Request $requestId] User: $user, Auth: $auth");
		$body = $this->request->getBody();
		log_message('info', "[Request $requestId] body: $body");
		$decoded_body = $this->bodyJsonToStr($body);
		log_message('info', "[Request $requestId] body decoded: $decoded_body");
	}

	// Get update parameters
	function getPostData() {
		if ($this->request->getMethod() != "post"){
			return false;
		}

		$body = $this->request->getBody();
		$content_type = $this->request->getHeaderLine('Content-Type');
		// var_dump($content_type);

		switch(strtolower($content_type))
		{
			case "application/json":
				$data = json_decode($body);
				break;
			case "text/xml":
				return false;
				break;
			default:
				return false;
				break;
		}

		return $data;
	}

	function bodyJsonToStr($body) {
		$ar = json_decode($body, true); // to array
		$output = '';
		if (isset($ar) && !empty($ar))
		foreach($ar as $key=>$value) {
			$output.="$key=>$value,";
		}
		return $output;
	}

}
