<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Router\Tests;

use Joomla\Router\RestRouter;
use Joomla\Test\TestHelper;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Stubs/GooGet.php';

/**
 * Test class for JApplicationWebRouterRest.
 *
 * @since  1.0
 */
class RestRouterTest extends TestCase
{
	/**
	 * @var    \Joomla\Router\RestRouter  The object to be tested.
	 * @since  1.0
	 */
	private $instance;

	/**
	 * @var    string  The server REQUEST_METHOD cached to keep it clean.
	 * @since  1.0
	 */
	private $requestMethod;

	/**
	 * Provides test data for testing fetch controller sufix
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestFetchControllerSuffix()
	{
		// Input, Expected
		return array(
			// Don't allow method in POST request
			array('GET', 'Get', null, false),
			array('POST', 'Create', "get", false),
			array('POST', 'Create', null, false),
			array('POST', 'Create', "post", false),
			array('PUT', 'Update', null, false),
			array('POST', 'Create', "put", false),
			array('PATCH', 'Update', null, false),
			array('POST', 'Create', "patch", false),
			array('DELETE', 'Delete', null, false),
			array('POST', 'Create', "delete", false),
			array('HEAD', 'Head', null, false),
			array('POST', 'Create', "head", false),
			array('OPTIONS', 'Options', null, false),
			array('POST', 'Create', "options", false),
			array('POST', 'Create', "foo", false),
			array('FOO', 'Create', "foo", true),

			// Allow method in POST request
			array('GET', 'Get', null, false, true),
			array('POST', 'Get', "get", false, true),
			array('POST', 'Create', null, false, true),
			array('POST', 'Create', "post", false, true),
			array('PUT', 'Update', null, false, true),
			array('POST', 'Update', "put", false, true),
			array('PATCH', 'Update', null, false, true),
			array('POST', 'Update', "patch", false, true),
			array('DELETE', 'Delete', null, false, true),
			array('POST', 'Delete', "delete", false, true),
			array('HEAD', 'Head', null, false, true),
			array('POST', 'Head', "head", false, true),
			array('OPTIONS', 'Options', null, false, true),
			array('POST', 'Options', "options", false, true),
			array('POST', 'Create', "foo", false, true),
			array('FOO', 'Create', "foo", true, true),
		);
	}

	/**
	 * Provides test data for the testParseRoute method.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public static function seedTestParseRoute()
	{
		// Method, Route, ControllerName, InputData
		return array(
			array('GET', 'articles/4', 'GooGet', array()),
		);
	}

	/**
	 * Tests the Joomla\Router\RestRouter::setHttpMethodSuffix method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Router\RestRouter::setHttpMethodSuffix
	 * @since   1.0
	 */
	public function testSetHttpMethodSuffix()
	{
		$this->instance->setHttpMethodSuffix('FOO', 'Bar');
		$s = TestHelper::getValue($this->instance, 'suffixMap');
		$this->assertEquals('Bar', $s['FOO']);
	}

	/**
	 * Tests the Joomla\Router\RestRouter::fetchControllerSuffix method.
	 *
	 * @param   string   $input        Input string to test.
	 * @param   string   $expected     Expected fetched string.
	 * @param   mixed    $method       Method to override POST request
	 * @param   boolean  $exception    True if an RuntimeException is expected based on invalid input
	 * @param   boolean  $allowMethod  Allow or not to pass method in post request as parameter
	 *
	 * @return  void
	 *
	 * @covers        Joomla\Router\RestRouter::fetchControllerSuffix
	 * @dataProvider  seedTestFetchControllerSuffix
	 * @since         1.0
	 */
	public function testFetchControllerSuffix($input, $expected, $method, $exception, $allowMethod=false)
	{
		TestHelper::invoke($this->instance, 'setMethodInPostRequest', $allowMethod);

		// Set reuqest method
		$_SERVER['REQUEST_METHOD'] = $input;

		// Set method in POST request
		$_GET['_method'] = $method;

		// If we are expecting an exception set it.
		if ($exception)
		{
			// expectException was added in PHPUnit 5.2 and setExpectedException removed in 6.0
			if (method_exists($this, 'expectException'))
			{
				$this->expectException('RuntimeException');
			}
			else
			{
				$this->setExpectedException('RuntimeException');
			}
		}

		// Execute the code to test.
		$actual = TestHelper::invoke($this->instance, 'fetchControllerSuffix');

		// Verify the value.
		$this->assertEquals($expected, $actual);
	}

	/**
	 * Tests the Joomla\Router\RestRouter::fetchControllerSuffix method if the suffix map is missing.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Router\RestRouter::fetchControllerSuffix
	 * @since   1.0
	 */
	public function testFetchControllerSuffixWithMissingSuffixMap()
	{
		$_SERVER['REQUEST_METHOD'] = 'FOOBAR';

		// expectException was added in PHPUnit 5.2 and setExpectedException removed in 6.0
		if (method_exists($this, 'expectException'))
		{
			$this->expectException('RuntimeException');
		}
		else
		{
			$this->setExpectedException('RuntimeException');
		}

		$suffix = TestHelper::invoke($this->instance, 'fetchControllerSuffix');
	}

	/**
	 * Tests the Joomla\Router\RestRouter::setMethodInPostRequest and isMethodInPostRequest.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Router\RestRouter::setMethodInPostRequest
	 * @covers  Joomla\Router\RestRouter::isMethodInPostRequest
	 * @since   1.0
	 */
	public function testMethodInPostRequest()
	{
		// Check the defaults
		$this->assertFalse(TestHelper::invoke($this->instance, 'isMethodInPostRequest'));

		// Check setting true
		TestHelper::invoke($this->instance, 'setMethodInPostRequest', true);
		$this->assertTrue(TestHelper::invoke($this->instance, 'isMethodInPostRequest'));

		// Check setting false
		TestHelper::invoke($this->instance, 'setMethodInPostRequest', false);
		$this->assertFalse(TestHelper::invoke($this->instance, 'isMethodInPostRequest'));
	}

	/**
	 * Tests the Joomla\Router\Router::parseRoute method.
	 *
	 * @param   string       $m  The request method.
	 * @param   string       $r  The route to parse.
	 * @param   string|null  $c  The expected controller name or null if an exception is expected.
	 * @param   array        $i  The expected input object data.
	 *
	 * @return  void
	 *
	 * @covers        Joomla\Router\RestRouter::parseRoute
	 * @dataProvider  seedTestParseRoute
	 * @since         1.0
	 */
	public function testParseRoute($m, $r, $c, $i)
	{
		// Set reuqest method
		$_SERVER['REQUEST_METHOD'] = $m;

		// Setup the router maps.
		$this->instance->setControllerPrefix('\Joomla\Router\Tests\Stubs\\')
			->addMaps(
				array(
					'articles/:article_id' => 'Goo',
				)
			);

		// If we should expect an exception set that up.
		if (is_null($c))
		{
			// expectException was added in PHPUnit 5.2 and setExpectedException removed in 6.0
			if (method_exists($this, 'expectException'))
			{
				$this->expectException('InvalidArgumentException');
			}
			else
			{
				$this->setExpectedException('InvalidArgumentException');
			}
		}

		// Execute the route parsing.
		$actual = TestHelper::invoke($this->instance, 'parseRoute', $r);

		// Test the assertions.
		$this->assertEquals($c, $actual, 'Incorrect controller name found.');
	}

	/**
	 * Prepares the environment before running a test.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->instance = new RestRouter;
		$this->requestMethod = @$_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Cleans up the environment after running a test.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function tearDown()
	{
		$this->instance = null;
		$_SERVER['REQUEST_METHOD'] = $this->requestMethod;

		parent::tearDown();
	}
}
