<?php namespace CodeIgniter\Test\Mock;

use CodeIgniter\HTTP\CURLRequest;

/**
 * Class MockCURLRequest
 *
 * Simply allows us to not actually call cURL during the
 * test runs. Instead, we can set the desired output
 * and get back the set options.
 */
class MockCURLRequest extends CURLRequest
{

	public $curl_options;
	protected $output = '';
	protected $filenames;

	//--------------------------------------------------------------------

	public function setOutput($output)
	{
		$this->output = $output;

		return $this;
	}

	/**
	 * Set headers for mock
	 *
	 * @param $headers array
	 */
	protected function setNativeheaders(array $headers) {
		$this->nativeheaders = $headers;
	}

	/**
	 * Set filename for downloaded files. One per request.
	 *
	 * @param $filenames array
	 */
	public function setFileNames(array $filenames) {
		$this->filenames = $filenames;
	}

	//--------------------------------------------------------------------

	protected function sendRequest(array $curl_options = [], $use_nativeheaders = null): string
	{
		// get next filename
		if (!empty($this->filenames)) {
			$filename = array_shift($this->filenames);
			$headerArray = $this->makeContentDispositionHeader($filename);
			$this->setNativeheaders($headerArray);
		}

		// Save so we can access later.
		$this->curl_options = $curl_options;

		return $this->output;
	}

	//--------------------------------------------------------------------
	// for testing purposes only
	public function getBaseURI()
	{
		return $this->baseURI;
	}

	// for testing purposes only
	public function getDelay()
	{
		return $this->delay;
	}

	/**
	 * Create header for downloaded filename
	 *
	 * @return array
	 */
	protected function makeContentDispositionHeader($filename) {
		return [
			"content-disposition:filename=UTF''$filename"
		];
	}

}
