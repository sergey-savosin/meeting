<?php namespace App\Controllers;

class Books extends BaseController {
	public function index() {
		
		// get data
		$page_data = array(
			[
		"id"=> 1,
		"title"=> "Little Women",
		"author"=> "Louisa May Alcott",
		"cover"=> "",
		"about"=> "Sisters learn to live in gratitude for each other.",
		"variation"=> 0
			],
			[
		"id"=> 2,
		"title"=> "Fahrenheit 451",
		"author"=> "Ray Bradbury",
		"cover"=> "",
		"about"=> "Enter a dystopian future where books with ideas are dangerous, controlled and burned.",
		"variation"=> 1
			],
			[
		"id"=> 3,
		"title"=> "Grapes of Wrath",
		"author"=> "John Steinbeck",
		"cover"=> "",
		"about"=> "Thousands set out from the dust bowl to California in search of better fortunes",
		"variation"=> 2,
		"favorite"=> true
			],
			[
		"id"=>4,
		"title"=> "To Kill a Mocking Bird",
		"author"=> "Harper Lee",
		"cover"=> "https://i.imgur.com/BPmn7bUm.jpg",
		"about"=> "A girl learns to trust the wisdom of her father and not be dismayed by the prejudice around here.",
		"variation"=> 0
			],
			[
		"id"=> 5,
		"title"=> "Moby Dick",
		"author"=> "Hermann Melville",
		"cover"=> "",
		"about"=> "Man hunts whale with a vengeance. In the end, he's the last man stand. I guess he won.",
		"variation"=> 1
			]
		);

		// Return result from service
		$json = json_encode($page_data);

		http_response_code(200); // 201: resource created

		$this->response->setHeader("Content-Type", "application/json");
		echo $json;

	}
}