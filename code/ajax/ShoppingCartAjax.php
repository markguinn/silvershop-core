<?php
/**
 * Ajax-specific functionality for shopping cart controller
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 04.07.2014
 * @package shop
 * @subpackage ajax
 */
class ShoppingCartAjax extends Extension {

	/**
	 * @param AjaxHTTPResponse $response
	 * @param SS_HTTPRequest $request
	 */
	public function updateAddResponse($response, SS_HTTPRequest $request) {
		$response->pushRegion('SideCart', $this->owner, array('Cart' => ShoppingCart::curr()));
		$response->triggerEvent('cartadd');
		$response->triggerEvent('cartchange', array(
			'action'    => 'add',
			'id'        => $request->param('ID'),
			'quantity'  => $request->requestVar('quantity'),
		));
	}

	/**
	 * @param AjaxHTTPResponse $response
	 * @param SS_HTTPRequest $request
	 */
	public function updateRemoveResponse($response, SS_HTTPRequest $request) {
		$response->pushRegion('SideCart', $this->owner, array('Cart' => ShoppingCart::curr()));
		$response->triggerEvent('cartremove');
		$response->triggerEvent('cartchange', array(
			'action'    => 'remove',
			'id'        => $request->param('ID'),
			'quantity'  => $request->requestVar('quantity'),
		));
	}

	/**
	 * @param AjaxHTTPResponse $response
	 * @param SS_HTTPRequest $request
	 */
	public function updateRemoveAllResponse($response, SS_HTTPRequest $request) {
		$response->pushRegion('SideCart', $this->owner, array('Cart' => ShoppingCart::curr()));
		$response->triggerEvent('cartremove');
		$response->triggerEvent('cartchange', array(
			'action'    => 'removeall',
			'id'        => $request->param('ID'),
			'quantity'  => 0,
		));
	}

	/**
	 * @param AjaxHTTPResponse $response
	 * @param SS_HTTPRequest $request
	 */
	public function updateSetQuantityResponse($response, SS_HTTPRequest $request) {
		$response->pushRegion('SideCart', $this->owner, array('Cart' => ShoppingCart::curr()));
		$response->triggerEvent('cartquantity');
		$response->triggerEvent('cartchange', array(
			'action'    => 'setquantity',
			'id'        => $request->param('ID'),
			'quantity'  => $request->requestVar('quantity'),
		));
	}

	/**
	 * @param AjaxHTTPResponse $response
	 * @param SS_HTTPRequest $request
	 */
	public function updateClearResponse($response, SS_HTTPRequest $request) {
		$response->pushRegion('SideCart', $this->owner, array('Cart' => ShoppingCart::curr()));
		$response->triggerEvent('cartclear');
		$response->triggerEvent('cartchange', array(
			'action'    => 'clear',
		));
	}

}