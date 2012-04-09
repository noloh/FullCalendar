<?php
//Path to NOLOH Kernel, change to your path
require_once("/var/www/htdocs/Projects/Asher/NOLOH/NOLOH.php");
System::IncludePaths('../');

class FullCalendarTest1 extends WebPage
{
	function __construct()
	{
		parent::WebPage('FullCalendar Example 1');
		$this->Controls->Add($calendar = new FullCalendar(array(FullCalendar::Month, FullCalendar::AgendaDay), 100, 100));
		// Create Button to Add Events
		$this->Controls->Add(new Button('Add'))
			->Click = new ServerEvent($this, 'AddEventFunc', $calendar);
		
		// Setting Events on the Calendar
		$calendar->DayClick = new ServerEvent($this, "DayFunc");
		$calendar->EventClick = new ServerEvent($this, "EventFunc");
	}
	function AddEventFunc($calendar)
	{
		$event = new FullCalendarEvent(str_repeat('Test Event! ', rand(1, 5)), strtotime('+' . (rand(1, 30)) . 'days'));
		$calendar->Events->Add($event);
	}
	function DayFunc()
	{
		System::Log(FullCalendar::$Data);
	}
	function EventFunc()
	{
		System::Log(FullCalendar::$Data);
	}
}
?>