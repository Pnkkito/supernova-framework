<?php
/**
 * Supernova Framework
 */
/**
 * Time parsers
 *
 * @package MVC_View_Time
 */
class Time {
	
	/**
	 * Format SQL to "nice" format
	 * @param	String	$date	SQL Date
	 * @return	String		Nice format
	 */
	public static function nice($date){
		$timeStamp = strtotime($date);
		// $week_days = array ("Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado");  
		// $months = array ("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");  
		$week_days = array ("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");  
		$months = array ("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");  
		$year_now = date ("Y",$timeStamp);  
		$month_now = date ("n",$timeStamp);
		$day_now = date ("j",$timeStamp);
		$week_day_now = date ("w",$timeStamp);
		$date = $week_days[$week_day_now] . ", " . $day_now . " " . $months[$month_now] . " " . $year_now;
		$time = date("H:i:s",$timeStamp);
		return $date.' / '.$time;
	}
	
	/**
	 * Parse date to SQL format
	 * @param	String	$date	Date string
	 * @return	String		SQL formated date
	 */
	public static function dateToSql($date){
		$date = str_replace('/','-',$date);
		$timeStamp = strtotime($date);
		if (defined('SQLDATEFORMAT')){
			$newDate = date(SQLDATEFORMAT, $timeStamp);
		}else{
			$newDate = date('Y-m-d H:i:s', $timeStamp);
		}
		return $newDate;
	}
	
	/**
	 * Parse SQL date to other date format
	 * @param	String	$date	SQL Date
	 * @return	String		parsed Date
	 */
	public static function sqlToDate($date){
		$timeStamp = strtotime($date);
		$newDate = date(DATEFORMAT, $timeStamp);
		return $newDate;
	}
	
	/**
	 * Parse timeStamp to SQL format
	 * @param	Int	$timeStamp	TimeStamp
	 * @return	String			SQL Format date
	 */
	public static function timestampToSQL($timeStamp){
		if (defined('SQLDATEFORMAT')){
			$newDate = date(SQLDATEFORMAT, $timeStamp);
		}else{
			$newDate = date('Y-m-d H:i:s', $timeStamp);
		}
		return $newDate;
	}
	
	/**
	 * Parse timeStamp to other date format
	 * @param	int	$timeStamp	TimeStamp
	 * @return	String			Date format
	 */
	public static function timestampToDate($timeStamp){
		if (defined('DATEFORMAT')){
			$newDate = date(DATEFORMAT, $timeStamp);
		}else{
			$newDate = date('Y-m-d H:i:s', $timeStamp);
		}
		return $newDate;
	}
	
}