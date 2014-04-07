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

	/**
	 * @param int            $errorCode
	 * @param SS_HTTPRequest $request
	 */
	public function onBeforeHTTPError($errorCode, SS_HTTPRequest $request) {
		// TODO: throw new SS_HTTPResponse_Exception($errorMessage, $errorCode);
	}

}