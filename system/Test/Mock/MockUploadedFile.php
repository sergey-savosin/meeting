<?php namespace CodeIgniter\Test\Mock;

// use CodeIgniter\HTTP\Files;
use CodeIgniter\HTTP\Files\UploadedFile;

class MockUploadedFile extends UploadedFile
{

	public function isValid(): bool
	{
		return True;
	}

}
