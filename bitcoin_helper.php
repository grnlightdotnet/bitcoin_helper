<?

// 	Bitcoin Helper 0.12

/*
	Bitcoin Helper Original Up to 0.11 - Copyright 2012, Jordan Hall
	http://jordanhall.co.uk/projects/bitcoin-helper-php-bitcoin-class/

	Bitcoin Helper 0.12 and Up - Copyright 2014, GrnLight.net
	http://grnlight.net

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*	Information about error codes

	Certain functions within Bitcoin Helper (0.11 onwards) will return
	numeric error codes in the case of unexpected error or failure.
	
	You should make sure to check the returned value for error codes
	(intergers less than zero) in your code. The following reference 
	shows the error numbers and an explanation.
	
	 * -1 = Network error retrieving data from bitcoincharts.com
	 * -2 = Error decoding JSON data retrieved from bitcoincharts.com
	 * -3 = Currency code not supported
	 * -4 = Could not write to cache file - check permissions!
*/

// bitcoin_helper - A class consisting of various functions relating to Bitcoin
class bitcoin_helper
{
	// convert_to_bitcoin - Converts a non-Bitcoin currency to Bitcoin and returns the Bitcoin value.
	// As of bitcoin_helper 0.11
	// currency_code = ISO 4217 formatted currency code, e.g. GBP, USD, etc.
	// amount = Amount of currency to convert to Bitcoin
	public function convert_to_btc($currency_code, $amount)
	{	
		// Get weighted prices. If an errors occurs, return the provided error code.
		$weighted_prices = $this->get_weighted_prices();
		if (is_numeric($weighted_prices) && $weighted_prices<0) return $weighted_prices;
		
		// Check if currency is supported
		if (!array_key_exists($currency_code, $weighted_prices)) return -3;
		if (!array_key_exists('24h', $weighted_prices[$currency_code])) return -3;
		
		// Perform necessary calculations and return Bitcoin amount
		$btc_amount = $amount / $weighted_prices[$currency_code]['24h'];
		return $btc_amount;
	}

	// get_btc_price - Just pulls the current 24 hour weighted price of bitcoin.
	// Like convert_to_bitcoin, $currency_code is ISO 4217 formatted currency code (see above)
	public function get_btc_price($currency_code)
	{	
		// Get weighted prices. If an errors occurs, return the provided error code.
		$weighted_prices = $this->get_weighted_prices();
		if (is_numeric($weighted_prices) && $weighted_prices<0) return $weighted_prices;
		
		// Check if currency is supported
		if (!array_key_exists($currency_code, $weighted_prices)) return -3;
		if (!array_key_exists('24h', $weighted_prices[$currency_code])) return -3;
		
		// Perform necessary calculations and return Bitcoin amount
		$btc_price = $weighted_prices[$currency_code]['24h'];
		return $btc_price;
	}

	
	// get_weighted_prices - Retrieves Bitcoin weighted prices JSON data from bitcoincharts.com and returns it as an array.
	// Caching: This function will attempt to cache (to a file) the JSON data retrieved from bitcoincharts.com for up to one hour.
	private function get_weighted_prices()
	{
		$cache_filename = "bitcoin_weighted_prices.json";
		//if (!is_writable($cache_filename)) return -4;
		if (file_exists($cache_filename) && filemtime($cache_filename)>=strtotime("-1 hour"))
		{
			$content = file_get_contents($cache_filename);
		}
		else
		{
			$url = "http://api.bitcoincharts.com/v1/weighted_prices.json";
			$content = @file_get_contents($url);
			if (!$content) return -1;
			file_put_contents($cache_filename, $content);
		}
		$json = json_decode($content, true);
		if (!$json) return -2;
		return $json;
	}

}
