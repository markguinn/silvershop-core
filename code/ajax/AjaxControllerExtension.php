<?php
/**
 * Catches errors and returns an AjaxHTTPResponse.
 * Could also add some helpers to controller for ajax functionality.
 * In the end this may or may not be needed?
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 04.03.2014
 * @package shop
 * @subpackage ajax
 */
class AjaxControllerExtension extends Extension {

	protected $ajaxResponse;

	/**
	 * @param int            $errorCode
	 * @param SS_HTTPRequest $request
	 */
	public function onBeforeHTTPError($errorCode, SS_HTTPRequest $request) {
		// TODO: This should probably prevent the error page from generating in ajax and possibly return a json response
		// throw new SS_HTTPResponse_Exception($errorMessage, $errorCode);
	}


	/**
	 * @return AjaxHTTPResponse
	 */
	public function getAjaxResponse() {
		if (!isset($this->ajaxResponse)) $this->ajaxResponse = new AjaxHTTPResponse($this->owner->getRequest());
		return $this->ajaxResponse;
	}

}