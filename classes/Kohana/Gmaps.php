<?php

Class Kohana_Gmaps {

	/**
	 * The URL to the Google Maps RESTfull API
	 */
	const API_URL = 'https://maps.googleapis.com/maps/api/';

	/**
	 * The url for the maps service
	 */
	const MAPS_URL = 'https://maps.google.com/';

	/**
	 * A list of supported Google Maps web services
	 */
	protected static $_APIs = array('geocode', 'distancematrix', 'staticmap');

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
		try
		{
			$parameters = array(
				'address' => $location,
			);

			$response = self::make_request('geocode', $parameters);
		}
		catch (Kohana_Exception $e)
		{
			return FALSE;
		}

		return $response['results'][0];
	}

	/**
	 * Determine the distance and travel time between two locations.
	 *
	 * @link https://developers.google.com/maps/documentation/distancematrix/
	 *
	 * @param  string  $origin      The start point
	 * @param  string  $destination The end point
	 * @param  boolean $imperial    Use imperial units?
	 * @return array
	 */
	public static function distance($origin, $destination, $imperial = TRUE)
	{
		try
		{
			// Setup the origin and destination
			$parameters = array(
				'origins'      => $origin,
				'destinations' => $destination,
			);

			// Should we use imperial units
			if ($imperial)
			{
				$parameters += array('units' => 'imperial');
			}

			$response = self::make_request('distancematrix', $parameters);
		}
		catch (Kohana_Exception $e)
		{
			return FALSE;
		}

		// Get the first element since we only support one currently
		$element = $response['rows'][0]['elements'][0];

		if (Arr::get($element, 'status') !== 'OK')
		{
			return FALSE;
		}

		return $element;
	}

	/**
	 * Generate a static map URL for use as an embedded image
	 *
	 * @link https://developers.google.com/maps/documentation/staticmaps/
	 *
	 * @param  string  $location The location to mark on the map
	 * @param  array   $options  Extra query parameters
	 * @param  boolean $geocode  Should the location be geocoded
	 * @return string
	 */
	public static function static_map($location, $options = array(), $geocode = TRUE)
	{
		// Geocode the location for more accurate results if necessary
		if ($geocode)
		{
			$location = Arr::get(self::geocode($location), 'formatted_address');
		}

		// Ensure the location isn't empty
		if ( ! $location) return FALSE;

		// The query parameters
		$params = array_merge(array(
			'center'  => $location,
			'markers' => $location,
			'sensor'  => 'false',
		), $options);

		return self::API_URL.'staticmap'.URL::query($params, FALSE);
	}

	/**
	 * Get the URL to Google maps of some specific location
	 *
	 * @param  string  $location The location to link to
	 * @param  boolean $geocode  Should the location be geocoded
	 * @return string
	 */
	public static function url($location, $geocode = TRUE)
	{
		// Geocode the location for more accurate results if necessary
		if ($geocode)
		{
			$location = Arr::get(self::geocode($location), 'formatted_address');
		}

		// Ensure the location isn't empty
		if ( ! $location) return FALSE;

		return self::MAPS_URL.URL::query(array('q' => $location), FALSE);
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

		// Setup the request URL
		$url = self::API_URL.$API.'/json'.URL::query($parameters, FALSE);

		// Check if the data was already cached (cached for two weeks)
		if (($cached = Kohana::cache($url, NULL, 1209600)) !== NULL)
		{
			// If the cached object was an invalid request invalidate it after a day
			if ($cached === FALSE)
			{
				Kohana::cache($url, NULL, 86400);
				throw new Kohana_Exception("Cached request is invalid");
			}

			return $cached;
		}

		try
		{
			// Make the request
			$response = Request::factory($url)->execute();

			// Make sure the response was successful
			if ($response->status() !== 200)
				throw new Kohana_Exception("Request unsuccessful, responded with HTTP :code", array(
					':code' => $response->status(),
				));

			// Convert the response JSON into a PHP array
			$json = json_decode($response->body(), TRUE);

			// Ensure that the API request executed with a "OK" status
			if ($json['status'] !== 'OK')
				throw new Kohana_Exception("Google Maps API responded with a :error error status", array(
					':error' => $json['status'],
				));

			// Cache the response object
			Kohana::cache($url, $json);

			// Return the decoded JSON as an array
			return $json;
		}
		catch (Exception $e)
		{
			// Cache the URL as an invalid request (FALSE)
			Kohana::cache($url, FALSE);
			throw $e;
		}
	}

}
