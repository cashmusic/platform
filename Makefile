
sqlite_schema:
	sqlt -f MySQL -t SQLite ./framework/php/settings/sql/cashmusic_db.sqlite > ./framework/php/settings/sql/cashmusic_db_sqlite.sql

sqlite_db:
	sqlite3 ./framework/php/db/cashmusic.sqlite < ./framework/php/settings/sql/cashmusic_db_sqlite.sql
