<?php
/**
 * Tests to cover AjaxHTTPResponse.
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 04.07.2014
 * @package shop
 * @subpackage tests
 */
class AjaxResponseTest extends SapphireTest {

	function testEmptyResponseJson() {
		$r = new AjaxHTTPResponse();
		$this->assertEquals('application/json', $r->getHeader('Content-type'));
		$this->assertEquals('{}', $r->getBody());
	}


	function testTriggerEvent() {
		$r = new AjaxHTTPResponse();
		$r->triggerEvent('cartchange', array('qty' => 4));
		$r->triggerEvent('addtocart');
		$json = $r->getBody();
		$data = json_decode($json, true);
		$this->assertNotEmpty($data[AjaxHTTPResponse::EVENTS_KEY], 'should contain an events key');
		$this->assertEquals(1, $data[AjaxHTTPResponse::EVENTS_KEY]['addtocart'], 'should contain a record of the addtocart event');
		$this->assertEquals(4, $data[AjaxHTTPResponse::EVENTS_KEY]['cartchange']['qty'], 'should correctly pass on the event data');
	}


	function testPushRegion() {
		$mock = new ArrayData(array('Cart' => ShoppingCart::curr()));
		$r = new AjaxHTTPResponse();
		$r->pushRegion('SideCart', $mock);
		$json = $r->getBody();
		$data = json_decode($json, true);
		$this->assertNotEmpty($data['SideCart'], 'should contain an entry for the side cart');
		$this->assertEquals($data['SideCart'], $mock->renderWith('SideCart')->forTemplate(), 'SideCart entry should match the sidecart template');
	}


	// TODO: pull region
	// TODO: http errors
	// TODO: html/xml output

}