<?php
/**
 * Created on 27.11.2006
 *
 * @author Gerd Riesselmann
 */

class HistoryTest extends GyroUnitTestCase {
	
	public function setUp() {
		Session::start();
		Session::pull('history');
	} 
	
	public function tearDown() {
		History::clear();
		Session::pull('history'); // Clear
	}

	public function test_get_empty() {
		$url = History::get(0);
		$this->assertIsA($url, Url);
		$this->assertEqual(Url::create(Config::get_url(Config::URL_DEFAULT_PAGE))->build(), $url->build());
		
		$url = History::get(2, Url::current());
		$this->assertIsA($url, Url);
		$this->assertEqual(Url::current()->build(), $url->build());
		
		$url = History::get(-1, 'http://www.example.org/test.html');
		$this->assertIsA($url, Url);
		$this->assertEqual('http://www.example.org/test.html', $url->build());
	}
	
	public function test_get_filled() {
		if (Session::is_started()) {
			History::push(Url::create('http://www.example.org/1.html'));
			History::push(Url::create('http://www.example.org/2.html'));
	
			$url = History::get(0);
			$this->assertIsA($url, Url);
			$this->assertEqual('http://www.example.org/2.html', $url->build());		
	
			$url = History::get(1);
			$this->assertIsA($url, Url);
			$this->assertEqual('http://www.example.org/1.html', $url->build());		
			
			$url = History::get(2);
			$this->assertIsA($url, Url);
			$this->assertEqual(Url::create(Config::get_url(Config::URL_DEFAULT_PAGE))->build(), $url->build());
			
			$url = History::get(3, Url::current());
			$this->assertIsA($url, Url);
			$this->assertEqual(Url::current()->build(), $url->build());
			
			$url = History::get(-1, 'http://www.example.org/test.html');
			$this->assertIsA($url, Url);
			$this->assertEqual('http://www.example.org/test.html', $url->build());
	
			// Check if history respects limits		
			for ($i = 0; $i < HISTORY_NUMBER_OF_ITEMS; $i++) {
				History::push(Url::create('http://www.example.org/this-url-was-put-in-history-by-gyro-simpletest.html'));
			}
			
			$url = History::get(HISTORY_NUMBER_OF_ITEMS);
			$this->assertIsA($url, Url);
			$this->assertEqual(Url::create(Config::get_url(Config::URL_DEFAULT_PAGE))->build(), $url->build());
		}
	}
}
?>