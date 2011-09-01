
sqlite_schema:
	sqlt -f MySQL -t SQLite ./framework/php/settings/sql/cashmusic_db.sql > ./framework/php/settings/sql/cashmusic_db_sqlite.sql

sqlite_db:
	sqlite3 ./framework/php/db/cashmusic.db < ./framework/php/settings/sql/cashmusic_db_sqlite.sql
