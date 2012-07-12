<?php
	/**
	 * Functions to add data to measurement tables from weatherstation files.
	 * Author: Jesse Blum (JMB)
	 * Date: 2012
	 */
	// Define the weather_station file folder ... this should be done dynamically...
	if ( !defined( 'HN_WS_FOLDER_PATH' ) )
		define( 'HN_WS_FOLDER_PATH',
				'http://localhost/wordpress/wp-content/uploads/weather/data/raw/' );
	// Define the weather_station_temperature table ... this should be done dynamically...
	if ( !defined( 'HN_TEMPERATURE_TABLE' ) )
		define( 'HN_TEMPERATURE_TABLE', 'wp_1_ts_temperature_23' );
	// Define the weather_station_humidity table ... this should be done dynamically...
	if ( !defined( 'HN_HUMIDITY_TABLE' ) )
		define( 'HN_HUMIDITY_TABLE', 'wp_1_ts_humidity_24' );
	// Define the weather_station_humidity table ... this should be done dynamically...
	if ( !defined( 'HN_PRESSURE_TABLE' ) )
		define( 'HN_PRESSURE_TABLE', 'wp_1_ts_Pressure_25' );// Define the weather_station_humidity table ... this should be done dynamically...
	if ( !defined( 'HN_WIND_AVE_TABLE' ) )
		define( 'HN_WIND_AVE_TABLE', 'wp_1_ts_Wind_Average_27' );// Define the weather_station_humidity table ... this should be done dynamically...
	if ( !defined( 'HN_WIND_GUST_TABLE' ) )
		define( 'HN_WIND_GUST_TABLE', 'wp_1_ts_Wind_Gust_28' );// Define the weather_station_humidity table ... this should be done dynamically...
	if ( !defined( 'HN_WIND_DIR_TABLE' ) )
		define( 'HN_WIND_DIR_TABLE', 'wp_1_ts_Wind_Dir_29' );// Define the weather_station_humidity table ... this should be done dynamically...
	if ( !defined( 'HN_RAIN_TABLE' ) )
		define( 'HN_RAIN_TABLE', 'wp_1_ts_Rain_Volume_30' );
	
	/**
	 * Fetches today's weather file and if it exists adds its readings to the DB
	 */
	function readWeatherFile(){
		$year = date('Y');
		$month = date('m');
		$day = date('d');
		$contents = file(
				HN_WS_FOLDER_PATH.
				"$year/$year-$month/$year-$month-$day.txt",
				FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	
		foreach($contents as $line){
			$fields = explode(",", $line);
			$timestamp = $fields[0];
			//$delay = $fields[1];
			//$hum_in = $fields[2];
			//$temp_in = $fields[3];
			$hum_out = $fields[4];
			$temp_out = $fields[5];
			$abs_pressure = $fields[6];
			$wind_ave = $fields[7];
			$wind_gust = $fields[8];
			$wind_dir = $fields[9];
			$rain = $fields[10];
			
			addReading(HN_TEMPERATURE_TABLE, $temp_out, $timestamp);
			addReading(HN_HUMIDITY_TABLE, $hum_out, $timestamp);
			addReading(HN_PRESSURE_TABLE, $abs_pressure, $timestamp);
			addReading(HN_WIND_AVE_TABLE, $wind_ave, $timestamp);
			addReading(HN_WIND_GUST_TABLE, $wind_gust, $timestamp);
			addReading(HN_WIND_DIR_TABLE, $wind_dir, $timestamp);
			addReading(HN_RAIN_TABLE, $rain, $timestamp);
		}
	}
	
	function addReading($table, $reading, $timestamp){
		$db = new Hn_TS_Database();
		
		$lastReading = $db->hn_ts_get_latest_reading(
				array('username','password',
						$table));
		if($lastReading){
			$lastDate = strtotime($lastReading->valid_time);
			$fieldDate = strtotime($timestamp);
			if($fieldDate > $lastDate){
				$db->hn_ts_insert_reading(
						array('username','password',
								$table, $reading,$timestamp )
				);
			}
		}else{
			$db->hn_ts_insert_reading(
					array('username','password',
							$table, $reading,$timestamp )
			);
		}
	}
?>