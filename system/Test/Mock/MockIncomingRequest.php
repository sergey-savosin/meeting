<?php namespace CodeIgniter\Test\Mock;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Files\FileCollection;

class MockIncomingRequest extends IncomingRequest
{
	protected $mockFiles = [];

	//    public function populateHeaders()
	//    {
	//        // Don't do anything... force the tester to manually set the headers they want.
	//    }

	public function detectURI($protocol, $baseURL)
	{
		// Do nothing...
	}

	/**
	* Настройка файлов для эмуляции _FILES
	*/
	public function setFile($controlName, $tmpName, $name, $type, $size)
	{
		$this->mockFiles[$controlName] = array(
			'tmp_name' => $tmpName,
			'name' => $name,
			'type' => $type,
			'size' => $size,
			'error' => null
		);
	}

	/**
	* Переопределение библиотечной функции getFiles
	*/
	public function getFiles(): array
	{
		// if (empty($this->mockFiles))
		// {
		// 	return array();
		// }

		$res = [];
		foreach ($this->mockFiles as $controlName => $file) {
			$res[$controlName] = array(
					new MockUploadedFile(
						$file['tmp_name'],
						$file['name'],
						$file['type'],
						$file['size'],
						$file['error']
					)
				);
		}
		return $res;
	}

	public function getFileMultiple(string $fileID)
	{
		// if (empty($this->mockFiles))
		// {
		// 	$this->mockFiles = new FileCollection();
		// }

		foreach ($this->mockFiles as $controlName => $file) {
			if ($controlName == $fileID) {
				return array(new MockUploadedFile(
					$file['tmp_name'],
					$file['name'],
					$file['type'],
					$file['size'],
					$file['error']
				));
			}
		}
	}

}
