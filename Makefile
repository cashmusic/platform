
sqlite_schema:
	sqlt -f MySQL -t SQLite ./framework/php/settings/sql/cashmusic_db.sql > ./framework/php/settings/sql/cashmusic_db_sqlite.sql

sqlite_db:
	sqlite3 cash.db < sqlite.sql
	sqlite3 cash.db < ./framework/php/settings/sql/cashmusic_db_sqlite.sql
