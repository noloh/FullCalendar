<?php
/**
*  FullCalendar Nodule Class
* 
*  NOLOH Nodule Wrapper of Adam Shaw's FullCalendar
*  http://arshaw.com/fullcalendar/
* 
*  Licensed Under MIT License
*  http://www.opensource.org/licenses/mit-license.html
*/
class FullCalendar extends Panel
{
	private static $CalendarPath = 'fullcalendar-1.5.3';
	/**
	* Types of Calendar Views
	*/
	const Month = 'month', Week = 'week', BasicWeek = 'basicWeek', 
	BasicDay = 'basicDay', AgendaWeek = 'agendaWeek', AgendaDay = 'agendaDay';
	/**
	* Information pertaining to an event that was triggered on the Calendar
	* 
	* @var mixed
	*/
	public static $Data;
	/**
	* Synched Event Data
	* @var mixed
	*/
	private $EventData;
	/**
	* Array holder for Configuration options
	* 
	* @var array(arrays)
	*/
	private $Config;
	/**
	* ArrayList of Events.
	* 
	* @var ImplicitArrayList
	*/
	private $Events;
	/**
	* Constructor
	* 
	* @param self::Month|self::Week|self::BasicWeek|self::BasicDay|self::AgendaWeek|self::AgendaDay|array(types) $type
	* @param integer $left
	* @param integer $top
	* @param mixed $width
	* @param mixed $height
	* @return Highcharts
	*/
	function __construct($type=self::Month, $left=0, $top=0, $width=900, $height=707)
	{
		parent::Panel($left, $top, $width, $height);
		$this->Events = new ImplicitArrayList($this, 'AddEvent', 'RemoveEventAt', 'ClearEvents');
		$this->SetDefaults();
		$this->SetType($type);
	}
	/**
	* Sets the defaults of this tinyMCE instance
	*/
	private function SetDefaults()
	{
		$this->Config = array();
		$this->Config['dayClick'] = ClientScript::Raw("function(date, allDay){_NSet('$this', '_NEventData', '{\"date\" : \"' + date + '\", \"allDay\" : ' + allDay + ', \"rand\" : ' + Math.random() + '}'); if(_N('$this').DayClick){_N('$this').DayClick()}}");
		$this->Config['eventClick'] = ClientScript::Raw("function(calEvent){_NSet('$this', '_NEventData', JSON.stringify(calEvent, function(k,v){return k == 'source'?Math.random():v})); if(_N('$this').EventClick){_N('$this').EventClick()}}");
		$this->SetHeaderTemplate();
	}
	/**
	* Set a FullCalendar configuration option. See {@link http://arshaw.com/fullcalendar/docs/} for all options.
	* 
	* @param array(arrays)|string $option
	* @param mixed $value
	*/
	function SetConfig($option, $value=null)
	{
		if(is_array($option) && !$value)
		{
			$this->Config = $option;
		}
		else
			$this->Config[$option] = $value;
//		$this->Refresh();
	}
	function GetConfig()	{return $this->Config;}
	/**
	* Re-renders the FullCalendar instance. In most cases there is no need to call manually.
	*/
	function Refresh($firstTime=false)
	{
		if(!$firstTime && $this->ShowStatus == Component::Shown)
		{
			ClientScript::RaceQueue($this, 'jQuery.fullCalendar', "\$('#{$this}').fullCalendar", array('destroy'));
		}
		elseif($this->ShowStatus != Component::Shown)
			return;
		ClientScript::RaceQueue($this, 'jQuery.fullCalendar', "\$('#{$this}').fullCalendar", array($this->Config));
	}
	public function GetEvents()	{return $this->Events;}
	/**
	 * Returns the Event associated with a Calendar day being clicked
	 * @return Event
	 */
	function GetDayClick()
	{
		return $this->GetEvent('DayClick');
	}
	/**
	 * Sets the Event associated with a Calendar day being clicked
	 * @param Event $event
	 */
	function SetDayClick($event)
	{
		return $this->SetEvent($event, 'DayClick');
	}
	/**
	 * Returns the Event associated with a Calendar's Event being clicked
	 * @return Event
	 */
	function GetEventClick()
	{
		return $this->GetEvent('EventClick');
	}
	/**
	 * Sets the Event associated with a Calendar's Event being clicked
	 * @param Event $event
	 */
	function SetEventClick($event)
	{
		return $this->SetEvent($event, 'EventClick');
	}
	/**
	* Called by Events ImplicitArrayList when adding an event
	* 
	* @param array|FullCalendarEvent $event
	*/
	function AddEvent($event)
	{
		if(is_array($event))
			$event = new FullCalendarEvent($event);
		if(!$event instanceof FullCalendarEvent)
			throw new Exception('Event must be an arrray or FullCalendarEvent object');
			
		if($this->ShowStatus == Component::Shown)
		{
			ClientScript::RaceQueue($this, 'jQuery.fullCalendar', "\$('#{$this}').fullCalendar", array('addEventSource', array($event->Properties)));
		}
		else
		{
			$this->Config['events'][] = $event->Properties;
		}
		$this->Events->Add($event, true);
	}
	/**
	* Called by Events ImplicitArrayList when Clearing an Events
	* 
	* @param array|FullCalendarEvent $event
	*/
	function ClearEvents()
	{
		if($this->ShowStatus == Component::Shown)
		{
			ClientScript::RaceQueue($this, 'jQuery.fullCalendar', "\$('#{$this}').fullCalendar", array('removeEvents'));
		}
		$this->Events->Clear(true);
	}
	/**
	* Sets the type of Calendar, whether it's month, week, day, agenda, etc.
	* If an array of types are passed in buttons will be added for each type
	* with the default being the first specified type
	* 
	* @param self::Month|self::Week|self::BasicWeek|self::BasicDay|self::AgendaWeek|self::AgendaDay|array(types) $type
	*/
	function SetType($type)
	{
		if(is_array($type))
		{
			$right = implode(',', $type);
			$this->Config['defaultView'] = $type[0];
			if(isset($this->Config['header']['right']))
				$right = ', ' . $right;
			$this->Config['header']['right'] .= $right;
		}
		else
		{
			$right = $type;
			$this->Config['defaultView'] = $type;
		}
		$this->Refresh();
	}
	/**
	 * @ignore
	 */
	public function Set_NEventData($data)
	{
		$this->EventData = json_decode($data, true);
		self::$Data = $this->EventData;
	}
	/**
	* Sets the Header Template
	*/
	function SetHeaderTemplate($left = 'title', $center = '', $right = 'today prev,next')
	{
		$this->Config['header']['left'] = $left;
		$this->Config['header']['center'] = $center;
		$this->Config['header']['right'] = $right;
		
		$this->Refresh();
	}
	/**
	* Do not call manually! Override of default Show(). Triggers when FullCalendar instance is initially shown.
	*/
	function Show()
	{
		parent::Show();
		$relativePath = System::GetRelativePath(getcwd(), dirname(__FILE__));
		//Add FullCalendar CSS
		WebPage::That()->CSSFiles->Add($relativePath . '/Vendor/' . self::$CalendarPath . '/fullcalendar/fullcalendar.css');
		//Add FullCalendar script files
		ClientScript::AddSource($relativePath . 'Vendor/' . self::$CalendarPath . '/jquery/' . 'jquery-1.7.2.min.js', false);
		ClientScript::AddSource($relativePath . 'Vendor/' . self::$CalendarPath . '/jquery/' . 'jquery-1.7.2.min.js', false);
		ClientScript::RaceAddSource('jQuery', $relativePath . 'Vendor/' . self::$CalendarPath . '/fullcalendar/' . 'fullcalendar.min.js');
		$this->Refresh(true);
	}
	/**
	* Generic function handler to handle for native non-handled FullCalendar calls.
	* For example: ChangeView(), Prev(), Next(), PrevYear(), NextYear(), Today(), GoToDate(), IncrementDate(), GetDate()
	* 
	* @param string $name Name of method you wish to call
	* @param mixed $args Method arguments
	*/
	function __call($name, $args)
	{
		if($this->HasMethod($name))
			parent::__call($name, $args);
		else
			ClientScript::RaceQueue($this, 'jQuery.fullCalendar', "\$('#{$this}').fullCalendar." . lcfirst($name) , $args);
	}
	
}
?>
