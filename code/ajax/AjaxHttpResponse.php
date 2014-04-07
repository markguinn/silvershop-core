<?php
/**
 * Special case of HTTP response that adds some helpers for ajax and
 * automatically handles the construction of the response.
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 04.03.2014
 * @package shop
 * @subpackage ajax
 */
class AjaxHTTPResponse extends SS_HTTPResponse {

	const EVENTS_KEY = '__events__';

	/** @var array - CLIENT-SIDE events that will be triggered on the document. Key=event, value=data for event handler */
	protected $events = array();

	/** @var array - Regions to send along. Key=Template, Value=HTML */
	protected $regions = array();


	/**
	 * Create a new HTTP response
	 *
	 * @param $body The body of the response
	 * @param $statusCode The numeric status code - 200, 404, etc
	 * @param $statusDescription The text to be given alongside the status code.
	 *  See {@link setStatusCode()} for more information.
	 */
	public function __construct($body = null, $statusCode = null, $statusDescription = null) {
		parent::__construct($body, $statusCode, $statusDescription);
		$this->addHeader('Content-type', 'application/json');
	}


	/**
	 * Queues up an event to be triggered on the client when the response is received.
	 * Events are not gauranteed to be triggered in order.
	 * Events are triggered AFTER regions are replaced.
	 *
	 * @param string $eventName
	 * @param mixed $eventData - must be json encodable
	 * @return $this
	 */
	public function triggerEvent($eventName, $eventData=1) {
		if (!empty($eventName)) {
			$this->events[$eventName] = $eventData;
		}

		return $this;
	}


	/**
	 * @param string|array $template
	 * @param ViewableData $renderObj [optional] - if not present, current controller will be used
	 * @param array $data [optional] - if present, renderObj will be customised with this data
	 * @return $this
	 */
	public function pushRegion($template, $renderObj=null, $data=null) {
		if (!empty($template)) {
			$regionID = is_array($template) ? $template[0] : $template;
			if (!$renderObj) $renderObj = Controller::curr();
			$this->regions[$regionID] = $renderObj->renderWith($template, $data)->forTemplate();
		}

		return $this;
	}


	/**
	 * @return string
	 */
	public function getBody() {
		// if the body has been set elsewhere, just pass that through.
		if (empty($this->body)) {
			$data = new StdClass;

			if (!empty($this->events)) {
				$key = self::EVENTS_KEY;
				$data->$key = $this->events;
			}

			if (!empty($this->regions)) {
				foreach ($this->regions as $key => $val) $data->$key = $val;
			}

			$this->setBody(json_encode($data));
		}

		return parent::getBody();
	}

}