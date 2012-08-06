<?php

Class Kohana_Gmaps {

	/**
	 * The URL to the Google Maps RESTfull API
	 */
	const URL = 'http://maps.googleapis.com/maps/api/';

	/**
	 * A list of supported Google Maps web services
	 */
	const APIS = array('geocode', 'distancematrix');

	/**
	 * Use Geocoding to determine the well formatted, fully qualified address of
	 * the passed location string.
	 *
	 * @link https://developers.google.com/maps/documentation/geocoding/
	 *
	 * @param  string $location The location to geocode
	 * @return string
	 */
	public static function geocode($location)
	{

	}

	/**
	 * Determine the distance and travel time between two locations.
	 *
	 * @param  string $origin      The start point
	 * @param  string $destination The end point
	 * @return array
	 */
	public static function distance($origin, $destination)
	{

	}

	/**
	 * Makes a HTTP request to the API specified
	 */
	protected static function make_request($API, $parameters)
	{
		// Always add the sensor parameter as false
		$parameters += array('sensor' => 'false');

		// Ensure that a valid API was requested
		if ( ! in_array($API, self::$_APIs))
			throw new Kohana_Exception("Invalid API");

		// Setup the requst URL
		$url = self::API_URL.$API.'/json'.URL::query($parameters, FALSE);

		// Make the request
		$response = Request::factory($url)->execute();

		// Make sure the response was sucessfull
		if ($response->status() !== 200)
			throw new Kohana_Exception("Request unsucessfull, resoded with HTTP :code", array(
				':code' => $response->status(),
			));

		// Convert the response JSON into a PHP array
		$json = json_decode($response->body(), TRUE);

		// Ensure that the API request executed with a "OK" status
		if ($json['status'] !== 'OK')
			throw new Kohana_Exception("Google Maps API responded with a :error error status", array(
				':error' => $json['status'],
			));

		// Return the decoded JSON as an array
		return $json;
	}

}
