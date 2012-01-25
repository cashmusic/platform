unit_test:
	php tests/php/all.php

integration_test:
	prove -lrv tests/integration

cleanup:
	php installers/php/test_uninstaller.php

test:
	-php tests/php/all.php
	php installers/php/test_uninstaller.php

fulltest: unit_test integration_test cleanup

documentation:
	php docs/generator/generatedocs.php

