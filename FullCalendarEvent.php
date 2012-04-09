<?php
class FullCalendarEvent extends Object
{
	private static $Allowed = array('id', 'title', 'allDay', 'start',
									'end', 'url', 'className', 'editable',
									'color', 'backgroundColor', 'borderColor', 'textColor');
	private $Properties;
	
	/**
	* put your comment there...
	* 
	* @param string|array $title If string, then will set the title, if array will pass in array to event
	* @param timestamp $start Start of event
	* @param timestamp $end End of event
	* @return FullCalendarEvent
	*/
	function __construct($title, $start, $end=null)
	{
		if(is_array($title))
			$this->Properties = $title;
		else
		{
			$this->Properties = array();
			$this->Title = $title;
			$this->Start = $start;
			if($end)
			{
				$this->End = $end;	
				$this->AllDay = false;
			}
		}
	}
	private function SetProperty($property, $value)
	{
		$this->Properties[$property] = $value;
		return $value;
	}
	public function GetProperties()	{return $this->Properties;}
	function __get($name)
	{
		$prop = strtolower($name);
		if(in_array($prop, self::$Allowed))
			return isset($this->Properties[$prop])?$this->Properties[$prop]:null;
		return parent::__get($name);
	}
	function __set($name, $value)
	{
		$prop = lcfirst($name);
		if(in_array($prop, self::$Allowed))
			return $this->SetProperty($prop, $value);
		return parent::__set($name, $value);
	}
	
}
?>