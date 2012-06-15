unit_test:
	php tests/php/all.php

integration_test:
	prove -lrv tests/integration

cleanup:
	php tests/php/test-uninstaller.php

install:
	php installers/php/dev_installer.php

test:
	-php tests/php/all.php
	php tests/php/test-uninstaller.php

citest:
	php tests/php/all.php

fulltest: unit_test integration_test cleanup
