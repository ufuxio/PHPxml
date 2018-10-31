<?php
	class blog{
		
		public $dize = array();
		public $newAr = array();
			
		public function __construct(){
			$this -> url = "http://blog.fiateknik.com/feed/";
			$this -> start = (int)0;
			$this -> limit = (int)12;
			$this -> titleLength = (int)60;
			$this -> contentLength = (int)130;
		}
			
		public function blog_curl(){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this -> url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			$isle = curl_exec($ch);
			curl_close($ch);
			return $isle;
		}
		
		public function xml_get(){
			$xml = new SimpleXMLElement($this -> blog_curl(), LIBXML_NOCDATA);
			return $xml;
		}
			
		public function blog_get(){
			foreach($this -> xml_get() as $k => $v):
				$dize[$k] = $v;
			endforeach;
			return $dize;
		}
		
		public function trim_text($input, $length, $ellipses = true, $strip_html = true) {
			if ($strip_html) {
				$input = strip_tags($input);
			}
			if (strlen($input) <= $length) {
				return $input;
			}
			$last_space = strrpos(substr($input, 0, $length), ' ');
			$trimmed_text = substr($input, 0, $last_space);
			
			if ($ellipses) {
				$trimmed_text .= '';
			}
			return $trimmed_text;
		}
		
		public function blog_filter(){
			$blogAll = json_decode( json_encode($this -> blog_get(), false));
			foreach($blogAll -> channel -> item as $key => $value) : 
				if($this -> start <= $this -> limit){
					preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $value->description, $image);
					$text = preg_replace('/<p\b[^>]*>(.*?)<\/p>/i', '', $value->description);
					$newAr[] = array(
						"title" => strlen($value -> title) >= $this -> titleLength ? $this -> trim_text($value -> title, $this -> titleLength)."..." : $value -> title ,
						"img" =>  str_replace("-150x150", "",$image[1]),
						"content" => $this -> trim_text($text, $this -> contentLength)."...",
						"link" => $value -> link,
						"date" => $value -> pubDate
					);
				}
				$this -> start++;
			endforeach;
			return json_decode( json_encode($newAr, false));
		}
		
	}
	
	$blog = new blog;
	$content = $blog -> blog_filter();
?>
