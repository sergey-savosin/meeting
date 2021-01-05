<?php namespace CodeIgniter\Test\Mock;

use CodeIgniter\HTTP\IncomingRequest;

class MockIncomingRequest extends IncomingRequest
{
	protected $files;

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
	public function setFile($controlName, $tmpName, $name, $type, $size) {
		if (empty($this->files)) {
			$this->files = [];
		}

		$this->files[$controlName] = array(
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
		if (!empty($this->files)) {
			$res = [];
			foreach ($this->files as $controlName => $file) {
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
		} else {
			return array();
		}
	}

}
