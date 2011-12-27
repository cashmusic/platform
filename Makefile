
sqlite_schema:
	sqlt -f MySQL -t SQLite ./framework/php/settings/sql/cashmusic_db.sqlite > ./framework/php/settings/sql/cashmusic_db_sqlite.sql

sqlite_db:
	sqlite3 ./framework/php/db/cashmusic.sqlite < ./framework/php/settings/sql/cashmusic_db_sqlite.sql

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
