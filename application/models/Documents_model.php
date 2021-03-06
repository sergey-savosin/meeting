<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Documents_model extends MY_Model {
	function __construct() {
		parent::__construct();
	}

	function fetch_documents($project_id) {
		$query = "SELECT *
		FROM document d
		WHERE d.doc_project_id = ?
		ORDER BY d.doc_id ASC
		";
		$result = $this->db->query($query, array($project_id));

		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	function new_document($filename, $projectId, $isforcreditor, $isfordebtor, $isformanager) {
		$data = array ('doc_project_id' => $projectId,
					'doc_filename' => $filename,
					'doc_body' => 'empty',
					'doc_is_for_creditor' => $isforcreditor,
					'doc_is_for_debtor' => $isfordebtor,
					'doc_is_for_manager' => $isformanager);
		if ($this->db->insert('document', $data)) {
			return $this->db->insert_id();
		} else {
			return false;
		}
	}

	function new_document_with_body($url, $filename, $projectId, $isforcreditor, $isfordebtor, $isformanager) {
		$ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
		$ck = 'NID=197=fOSKSSxLFKeCpm7hlXff0qJ_HBd-wLDFgGH7mj37pPvivWyYVG7HqhZrKWIN_9g3jxy1fLr-dcQaqlrBeMxoOd3CugsR0bl00cU6coMstYaukQvCCqDwkSIUVfZNserollFirVBkMrqpmEvoEbrvXOqUbqDuLE5yqpLV69kmvtc; expires=Sun, 02-Aug-2020 15:48:51 GMT; path=/; domain=.google.com; HttpOnly';
		//$ckfile = tempnam ("/domains/vprofy.ru/tmp", "CURLCOOKIE");
		$ckfile = "";

		/*
			for debug use
			CURLOPT_RETURNTRANSFER = true - вывод в переменную $curlres
			CURLOPT_HEADER = true - вывод включает заголовки
			printf("%s\r\n",$curlres); - вывод переменной в веб-ответ
		*/

		$headers = [];

		$options = array(
			CURLOPT_TIMEOUT => 600, // set this to 10 minutes so we dont timeout on big files
			CURLOPT_RETURNTRANSFER => true, //!
			CURLOPT_VERBOSE => true,
			CURLOPT_COOKIE => $ck,
			CURLOPT_COOKIEJAR => $ckfile,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 20,
			CURLOPT_URL     => $url
		);

		$ch = curl_init();
		curl_setopt_array($ch, $options);

		// this function is called by curl for each header received
		curl_setopt($ch, CURLOPT_HEADERFUNCTION,
			function($curl, $header) use (&$headers)
			{
			    $len = strlen($header);
			    $header = explode(':', $header, 2);
			    if (count($header) < 2) // ignore invalid headers
			        return $len;
			    
			    $headers[strtolower(trim($header[0]))][] = trim($header[1]);
			    
			    return $len;
			}
		);

		$curlres = curl_exec($ch);

		$contentDisposition = $headers["content-disposition"][0];
		$newFileName = $this->extractFileName($headers["content-disposition"][0]);
		//var_dump($headers["content-disposition"][0]);
		//var_dump($newFileName);

		if (($newFileName) && !empty($newFileName) && 1==1)
		{
			$outFileName = $newFileName;
			$msg = 'using external filename: '.$outFileName;
		} else {
			$outFileName = $filename;
			$msg = 'using provided filename: '.$outFileName;
		}
		$this->log_debug('new_document_with_body', $msg);

		$url_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// Validate url_status
		if (empty($url_status))
		{
			$msg = "No HTTP code was returned. URL: ".$url;
			$this->log_debug('new_document_with_body', $msg);

			throw new Exception($msg."\r\n");
		}

		if ($url_status<>200)
		{
			$msg = "Can not download file from URL. Response code: ".$url_status.". URL: ".$url;
			$this->log_debug('new_document_with_body', $msg."\r\n");

			throw new Exception($msg);
		}

		// Save file to database
		$data = array ('doc_project_id' => $projectId,
					'doc_filename' => $outFileName,
					'doc_body' => $curlres,
					'doc_is_for_creditor' => $isforcreditor,
					'doc_is_for_debtor' => $isfordebtor,
					'doc_is_for_manager' => $isformanager);
		if ($this->db->insert('document', $data)) {
			$doc_id = $this->db->insert_id();
		} else {
			$doc_id = false;
		}

		// Close curl
		curl_close($ch);

		return $doc_id;
	}

	/**
	* public function
	* Detect file direct download URL
	* 
	* @url - url to download from
	* @requestId - for debug journal
	*/
	function correctFileDownloadUrl($url)
	{
		$url = str_replace('\\', "", $url);
		$msg = 'Source Url: '. $url;
		$this->log_debug('correctFileDownloadUrl', $msg);

		$domain = parse_url($url, PHP_URL_HOST);
		$query = parse_url($url, PHP_URL_QUERY);
		$path2 = parse_url($url, PHP_URL_PATH);
		parse_str($query, $parsedQuery);

		//printf("<br><hr><br>Domain: %s <br>", $domain);
		//printf("Path: %s<br>", $path2);
		//printf("Query: %s<br>", $query);
		//printf("Parsed query: %s<br>", $parsedQuery['id']);

		if (strtolower($domain)==="drive.google.com")
		{
			$this->log_correctFileDownloadUrl('found drive.goodle.com');

			// try to detect https://drive.google.com/open?id=[FILE_ID]
			if (!empty($parsedQuery['id']))
			{
				$this->log_debug('correctFileDownloadUrl', "found document id: ".$parsedQuery['id']);
				$newlink = 'https://docs.google.com/uc?id='.$parsedQuery['id'].'&export=download';
			}
			else
			{
				// try to detect https://drive.google.com/file/d/[FILE_ID]
				$exploded = explode('/',$path2);

				$found = false;
				$i=0;
				while (1==1)
				{
					if ($exploded[$i]==="")
					{
						$i+=1;
						continue;
					}
					elseif ($exploded[$i]==="d")
					{
						$i+=1;
						continue;
					}
					elseif ($exploded[$i]==="file")
					{
						$i+=1;
						$found = true;
						continue;
					}

					if ($found)
					{
						//$msg = "Exploded path. Found id: ".$exploded[$i];
						$this->log_debug('correctFileDownloadUrl', "Exploded path. Found id: $exploded[$i]");
						$newlink = 'https://docs.google.com/uc?id='.$exploded[$i].'&export=download';
					}
					break;
				}
			}
		}

		if (strtolower($domain)==="docs.google.com")
		{
			$this->log_debug('correctFileDownloadUrl', 'found docs.goodle.com');
			// try to detect https://docs.google.com/document/d/[FILE_ID]

			$exploded = explode('/',$path2);

			$found = false;
			$i=0;
			while (1==1)
			{
				if ($exploded[$i]==="")
				{
					$i+=1;
					continue;
				}
				elseif ($exploded[$i]==="d")
				{
					$i+=1;
					continue;
				}
				elseif ($exploded[$i]==="document")
				{
					$i+=1;
					$found = true;
					continue;
				}

				if ($found)
				{
					//$msg = "Exploded path. Found id: ".$exploded[$i];
					$this->log_debug('correctFileDownloadUrl', "Exploded path. Found id: $exploded[$i]");
					//$newlink = 'https://docs.google.com/uc?id='.$exploded[$i].'&export=download';
					$newlink = 'https://docs.google.com/document/d/'.$exploded[$i].'/export?format=docx';
					//ToDo: определять формат по имени файла
				}
				
				break;
			}

		}

		// Default
		if (!isset($newlink))
		{
			$this->log_debug('correctFileDownloadUrl', 'Nothing detected. Use source Url');
			$newlink = $url;
		}

		//$msg = "Result URL for download: ".$newlink;
		$this->log_debug('correctFileDownloadUrl', "Result URL for download: $newlink");

		return $newlink;
	}

/*
-----------------------
Try encoding it with base64 before saving in your database field:

echo base64_encode($picture1);
And to read out from database, you need to decode it:

echo base64_decode($picture);
But be sure to send the right headers with it, for example:

header('Content-Type: image/png');
------------------------
*/

	/**
	* public function
	* Upload file to a ReadDocument service (DB or file system)
	* 
	* @url - url to download from
	* @filename - name of new file
	* @targetdir - directory to save the new file
	* 
	* Return value:
	*	true = ok
	*	false = error
	* ToDo: save content-disposition to DB?
	*/
	function uploadFileToService($url, $filename, $targetdir)
	{
		// 1. validate params
		if (!isset($url) || empty($url) || $url === "")
		{
			$this->log_debug('uploadFileToService', "Empty Uri value in request body.");
			return false;
		}

		// 2. download file to local dir
		$fn = $targetdir.'/'.$filename;
		try
		{
			$this->downloadUrlToFile($url, $fn);
		}
		catch (Exception $e)
		{
			$msg = $e->getMessage();
			$this->log_debug('uploadFileToService', "exception: $msg");
			return false;
		}

		// 3. Response to client

		$msg = "File successful uploaded to service. File name: ".$fn.". Uri: ".$url;
		$this->log_debug('uploadFileToService', $msg);

		return true; // ok
	}

	/**
	* private function
	* Download file from url to local dir
	* @url - url to download from
	* @outFileName - path and filename where to store a new file

	* raises exceptions!
	*/
	function downloadUrlToFile($url, $outFileName)
	{

		$ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
		$ck = 'NID=197=fOSKSSxLFKeCpm7hlXff0qJ_HBd-wLDFgGH7mj37pPvivWyYVG7HqhZrKWIN_9g3jxy1fLr-dcQaqlrBeMxoOd3CugsR0bl00cU6coMstYaukQvCCqDwkSIUVfZNserollFirVBkMrqpmEvoEbrvXOqUbqDuLE5yqpLV69kmvtc; expires=Sun, 02-Aug-2020 15:48:51 GMT; path=/; domain=.google.com; HttpOnly';
		//$ckfile = tempnam ("/domains/vprofy.ru/tmp", "CURLCOOKIE");
		$ckfile = "";

		/*
			for debug use
			CURLOPT_RETURNTRANSFER = true - вывод в переменную $curlres
			CURLOPT_HEADER = true - вывод включает заголовки
			printf("%s\r\n",$curlres); - вывод переменной в веб-ответ
		*/

		$headers = [];

		$options = array(
			CURLOPT_TIMEOUT => 600, // set this to 10 minutes so we dont timeout on big files
			CURLOPT_RETURNTRANSFER => true, //!
			CURLOPT_VERBOSE => true,
			CURLOPT_COOKIE => $ck,
			CURLOPT_COOKIEJAR => $ckfile,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 20,
			CURLOPT_URL     => $url
		);

		$ch = curl_init();
		curl_setopt_array($ch, $options);

		// this function is called by curl for each header received
		curl_setopt($ch, CURLOPT_HEADERFUNCTION,
			function($curl, $header) use (&$headers)
			{
			    $len = strlen($header);
			    $header = explode(':', $header, 2);
			    if (count($header) < 2) // ignore invalid headers
			        return $len;
			    
			    $headers[strtolower(trim($header[0]))][] = trim($header[1]);
			    
			    return $len;
			}
		);

		$curlres = curl_exec($ch);

		$contentDisposition = $headers["content-disposition"][0];
		$newFileName = $this->extractFileName($headers["content-disposition"][0]);
		//var_dump($headers["content-disposition"][0]);
		//var_dump($newFileName);

		if (($newFileName) && !empty($newFileName) && 1==2)
		{
			$outFileName = $newFileName;
		}
		$msg = 'using provided filename: '.$outFileName;
		$this->log_debug('downloadUrlToFile', $msg);

		$fp = fopen($outFileName, 'w');
		if ( !$fp ) {
			$msg = 'Failed to open local file:'.$outFileName;
			$this->log_debug('downloadUrlToFile', $msg);

			throw new Exception($msg);
		}

		fwrite($fp, $curlres);

		$url_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);
		fclose($fp);

		if (empty($url_status))
		{
			unlink($outFileName);

			$msg = "No HTTP code was returned. URL: ".$url;
			$this->log_debug('downloadUrlToFile', $msg);

			throw new Exception($msg."\r\n");
		}

		if ($url_status<>200)
		{
			unlink($outFileName);

			$msg = "Can not download file from URL. Response code: ".$url_status.". URL: ".$url;
			$this->log_debug('downloadUrlToFile', $msg."\r\n");

			throw new Exception($msg);
		}
	}

	function downloadUrlToDb($url)
	{

		$ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
		$ck = 'NID=197=fOSKSSxLFKeCpm7hlXff0qJ_HBd-wLDFgGH7mj37pPvivWyYVG7HqhZrKWIN_9g3jxy1fLr-dcQaqlrBeMxoOd3CugsR0bl00cU6coMstYaukQvCCqDwkSIUVfZNserollFirVBkMrqpmEvoEbrvXOqUbqDuLE5yqpLV69kmvtc; expires=Sun, 02-Aug-2020 15:48:51 GMT; path=/; domain=.google.com; HttpOnly';
		//$ckfile = tempnam ("/domains/vprofy.ru/tmp", "CURLCOOKIE");
		$ckfile = "";

		/*
			for debug use
			CURLOPT_RETURNTRANSFER = true - вывод в переменную $curlres
			CURLOPT_HEADER = true - вывод включает заголовки
			printf("%s\r\n",$curlres); - вывод переменной в веб-ответ
		*/

		$headers = [];

		$options = array(
			CURLOPT_TIMEOUT => 600, // set this to 10 minutes so we dont timeout on big files
			CURLOPT_RETURNTRANSFER => true, //!
			CURLOPT_VERBOSE => true,
			CURLOPT_COOKIE => $ck,
			CURLOPT_COOKIEJAR => $ckfile,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 20,
			CURLOPT_URL     => $url
		);

		$ch = curl_init();
		curl_setopt_array($ch, $options);

		// this function is called by curl for each header received
		curl_setopt($ch, CURLOPT_HEADERFUNCTION,
			function($curl, $header) use (&$headers)
			{
			    $len = strlen($header);
			    $header = explode(':', $header, 2);
			    if (count($header) < 2) // ignore invalid headers
			        return $len;
			    
			    $headers[strtolower(trim($header[0]))][] = trim($header[1]);
			    
			    return $len;
			}
		);

		$curlres = curl_exec($ch);

		$contentDisposition = $headers["content-disposition"][0];
		$newFileName = $this->extractFileName($headers["content-disposition"][0]);
		//var_dump($headers["content-disposition"][0]);
		//var_dump($newFileName);
		//$this->log_debug('downloadUrlToFile - contentDsp', $contentDisposition);

		if (($newFileName) && !empty($newFileName) && 1==2)
		{
			$outFileName = $newFileName;
		}
		$msg = 'using provided filename: '.$outFileName;
		$this->log_debug('downloadUrlToFile', $msg);

		$fp = fopen($outFileName, 'w');
		if ( !$fp ) {
			$msg = 'Failed to open local file:'.$outFileName;
			$this->log_debug('downloadUrlToFile', $msg);

			throw new Exception($msg);
		}

		fwrite($fp, $curlres);

		$url_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);
		fclose($fp);

		if (empty($url_status))
		{
			unlink($outFileName);

			$msg = "No HTTP code was returned. URL: ".$url;
			$this->log_debug('downloadUrlToFile', $msg);

			throw new Exception($msg."\r\n");
		}

		if ($url_status<>200)
		{
			unlink($outFileName);

			$msg = "Can not download file from URL. Response code: ".$url_status.". URL: ".$url;
			$this->log_debug('downloadUrlToFile', $msg."\r\n");

			throw new Exception($msg);
		}
	}

	/**********************************
	* Извление имени файла из заголовка content-disposition
	* @header - заголовок content-disposition
	* @requestId - для журнала отладки

	* Возвращает: имя файла в случае успеха. Иначе - false.
	****/
	function extractFileName($header)
	{
		$str = urldecode($header);
		//$res = preg_match_all("/[^\'\']+$/", $str, $output);

		// 1. Ищем подстроку вида: filename*=UTF-8''3.%20%D0%9E%D1%82%D1%87%D0%B5.pdf
		$res = preg_match("/(?<=\'\')(.)+$/", $str, $output);
		//print("match result1: ".$res);
		//print("<br>=> ");
		//var_dump($output);

		if (!empty($output[0]))
		{
			$msg = "Extracted UTF filename: ".$output[0];
			$this->log_debug('extractFileName', $msg);

			return $output[0];
		}

		// 2. Ищем подстроку вида: filename="3. _____ ___________ ____________.pdf";
		$res = preg_match('/(?<=filename=")([^"])+/', $str, $output);
		//print("match result2: ".$res);
		//print("<br>=> ");
		//var_dump($output);
		if (!empty($output[0]))
		{
			$msg = "Extracted plain filename: ".$output[0];
			$this->log_debug('extractFileName', $msg);

			return $output[0];
		}

		$msg = "No filename extracted from header!";
		$this->log_debug('extractFileName', $msg);

		return false;
	}

	function get_document($doc_id) {
		$query = "SELECT d.doc_body, d.doc_filename,
			d.doc_project_id, d.doc_is_for_creditor, 
			d.doc_is_for_debtor, d.doc_is_for_manager
		FROM document d
		WHERE d.doc_id = ?
		";
		$result = $this->db->query($query, array($doc_id));

		if ($result) {
			return $result;
		} else {
			return false;
		}
	}
}