import re
import os

sql_path = r"C:\laragon\www\usama-fandaqah\.kilo\worktrees\ripple-jitterbug\schema_fandaqah_ready.sql"
migrations_dir = r"C:\laragon\www\usama-fandaqah\database\migrations"

# 1. Get all tables from SQL
with open(sql_path, 'r', encoding='utf-8') as f:
    content = f.read()

sql_tables = re.findall(r"CREATE TABLE IF NOT EXISTS `([^`]+)`", content)
print(f"Total tables in SQL: {len(sql_tables)}")

# 2. Get all tables currently in migrations
migrated_tables = []
for filename in os.listdir(migrations_dir):
    if filename.endswith(".php"):
        with open(os.path.join(migrations_dir, filename), 'r', encoding='utf-8') as f:
            m_content = f.read()
            matches = re.findall(r"Schema::create\('([^']+)'", m_content)
            migrated_tables.extend(matches)

migrated_tables = list(set(migrated_tables))
print(f"Total tables in migrations: {len(migrated_tables)}")

# 3. Find missing tables
missing_tables = [t for t in sql_tables if t not in migrated_tables]
print(f"Missing tables count: {len(missing_tables)}")

with open("missing_tables.txt", "w") as f:
    for t in sorted(missing_tables):
        f.write(t + "\n")

print("Missing tables saved to missing_tables.txt")
