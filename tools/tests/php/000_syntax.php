<?php

require_once(dirname(__FILE__) . '/base.php');


class SyntaxTests extends UnitTestCase {	
	public function checkSyntax($file) {
		$output = exec("php -l $file", $array, $code);
		$this->assertTrue($code == 0, $output);
	}

	public function testFrameworkSyntax() {
		echo "Testing Syntax\n";

		$test_files = glob(__DIR__ . '/../../../framework/*/*.php');
		$test_files = array_merge($test_files,glob(__DIR__ . '/../../../framework/classes/*/*.php'));
		$test_files[] = __DIR__ . '/../../../framework/cashmusic.php';
		foreach ($test_files as $file){
			$this->checkSyntax($file);
		}
	}

	public function testInterfaceSyntax() {
		$test_files = glob(__DIR__ . '/../../../interfaces/*/*.php');
		$test_files = array_merge($test_files,glob(__DIR__ . '/../../../interfaces/*/*/*.php'));
		$test_files = array_merge($test_files,glob(__DIR__ . '/../../../interfaces/*/*/*/*.php'));
		foreach ($test_files as $file){
			$this->checkSyntax($file);
		}
	}

	public function testJSONSyntax() {
		$test_files = glob(__DIR__ . '/../../../interfaces/admin/components/text/*/*/*.json');
		$test_files = array_merge($test_files,glob(__DIR__ . '/../../../framework/settings/connections/*.json'));
		foreach ($test_files as $file){
			// bad JSON returns null, so just test for true
			$this->assertTrue(json_decode(file_get_contents($file)),'Invalid JSON in [' . str_replace(__DIR__ . '/../../../','',$file) . ']');
		}
	}
}
?>
