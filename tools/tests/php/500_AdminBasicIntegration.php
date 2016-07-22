<?php
require_once(dirname(__FILE__) . '/base.php');
require_once(dirname(__FILE__) . '/curl.php');

class AdminBasicIntegration extends UnitTestCase {
	public $cc;
	private $cash_test_url;
	private $cash_user_login;
	private $cash_user_password;

	public function __construct() {
		$this->cc = new cURL();
		$this->cash_test_url=getTestEnv("CASHMUSIC_TEST_URL");
		$this->cash_user_login=getTestEnv("CASH_CI_LOGIN");
		$this->cash_user_password=getTestEnv("CASH_CI_PASSWORD");
		if (!$this->cash_test_url) {
			$this->cash_test_url = 'http://localhost';
		}
		// force a static login for CI workers (Travis, etc):
		if (!$this->cash_user_login) {
			$this->cash_user_login = 'dev@cashmusic.org';
			$this->cash_user_password = 'dev';
			$user_add_request = new CASHRequest(
				array(
					'cash_request_type' => 'system',
					'cash_action' => 'addlogin',
					'address' => $this->cash_user_login,
					'password' => $this->cash_user_password,
					'is_admin' => true
				)
			);
			$this->cash_user_id = $user_add_request->response['payload'];
		}
		if ($this->cash_test_url == 'http://dev.cashmusic.org') {
			echo "Test URL is pointing to an external test server, skipping integration tests.\n";
			$this->cash_test_url = false;
		} else {
			echo "\nTesting basic admin integration at:\n" . $this->cash_test_url . "\n";
			echo "using login: " . $this->cash_user_login . " / " . $this->cash_user_password . "\n\n";
		}
    }

	public function testLogin() {
		if ($this->cash_test_url) {
			// make sure we get the login page
			$src = $this->cc->get($this->cash_test_url . '/admin/');
			$this->assertPattern('/Log in/', $src);

			// look for an incorrect login
			$src = $this->cc->post(
				$this->cash_test_url . '/admin/',
				http_build_query(array(
					'address'            => 'false@example.com',
					'password'           => 'incorrect',
					'login'              => '1'
				))
			);
			$this->assertPattern('/Log in/', $src); // login page

			// now try a good login
			$prevsrc = $src;
			$src = $this->cc->post(
				$this->cash_test_url . '/admin/',
				http_build_query(array(
					'address'            => $this->cash_user_login,
					'password'           => $this->cash_user_password,
					'login'              => '1'
				))
			);
			$this->assertNoPattern('/Log in/', $src);
			$this->assertNotEqual($prevsrc,$src);

			// make sure the cookie is persistent
			$prevsrc = $src;
			$src = $this->cc->get($this->cash_test_url . '/admin/');
			$this->assertNoPattern('/Log in/', $src);
		}
    }

    public function testAllRoutes() {
    	if ($this->cash_test_url) {
	    	// run through all known routes and make sure we're getting pages, not error messages
	    	$all_routes = json_decode(file_get_contents(dirname(__FILE__) . '/../../../interfaces/admin/components/interface/en/menu.json'),true);
			$prevsrc = '';
			foreach ($all_routes as $route => $details) {
				echo '    Testing route: ' . $this->cash_test_url . '/admin/' .  $route . "\n";
	    		$src = $this->cc->get($this->cash_test_url . '/admin/' . $route);
				// test for basic html structures. are pages rendering completely, etc?
				if (!strpos($route,'export')) {
					$this->assertPattern('/<html/', $src);
		    		$this->assertPattern('/<body/', $src);
		    		$this->assertPattern('/<\/body/', $src);
		    		$this->assertPattern('/<\/html/', $src);
				}

				// a lot of the "elements" routes will default back to the main page, therefore being identical
				// so we'll skip those and focus on testing different routes
				if (strpos($route,'elements') == -1) {
					// and the page has changed with the route?
					$this->assertNotEqual($prevsrc,$src);
				}

				// test the content with some exceptions
				if (
					$route != 'campaigns/archive' &&
					$route != 'commerce' &&
					$route != 'commerce/items/edit' &&
					$route != 'commerce/items/delete' &&
					$route != 'commerce/orders/view' &&
					$route != 'people/lists/export' &&
					$route != 'people/lists/view' &&
					$route != 'people/mailings' &&
					$route != 'elements/templates/add' &&
					$route != 'elements/add' &&
					$route != 'elements/edit' &&
					$route != 'elements/stats' &&
					$route != 'settings/connections'
				) {
					// now check that the proper text is being displayed
					$filename = dirname(__FILE__) . '/../../../interfaces/admin/components/text/en/pages/'.
									str_replace('/','_',$route) .
									'.json';
					if (file_exists($filename)) {
						$text = json_decode(file_get_contents($filename),true);
						if (is_array($text)) {
							if (count($text['copy'])) {
								$keys = array_keys($text['copy']);
								$this->assertTrue(strpos($src,$text['copy'][$keys[0]]));
							}
							if (count($text['labels'])) {
								$keys = array_keys($text['labels']);
								$this->assertTrue(strpos($src,$text['labels'][$keys[0]]));
							}
						}
					}
				}

				$prevsrc = $src;
	    	}
			echo "\n";
    	}
    }

}
?>
