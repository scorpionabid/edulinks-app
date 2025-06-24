-- EduLinks Database Installation Script
-- This script creates the complete database structure and inserts default data

-- Create database (run this manually as postgres superuser)
-- CREATE DATABASE edulinks_db;
-- CREATE USER edulinks_user WITH PASSWORD 'secure_password';
-- GRANT ALL PRIVILEGES ON DATABASE edulinks_db TO edulinks_user;

\echo 'Installing EduLinks Database...'

-- Run migrations in order
\i migrations/001_create_users_table.sql
\echo 'Users table created.'

\i migrations/002_create_pages_table.sql
\echo 'Pages table created.'

\i migrations/003_create_links_table.sql
\echo 'Links table created.'

\i migrations/004_create_permissions_table.sql
\echo 'Permissions table created.'

\i migrations/005_create_settings_table.sql
\echo 'Settings table created.'

\i migrations/006_create_functions_triggers.sql
\echo 'Functions and triggers created.'

-- Insert default data
\i seeds/default_admin.sql
\echo 'Default admin user created.'

\i seeds/default_pages.sql
\echo 'Default pages created.'

\i seeds/default_settings.sql
\echo 'Default settings created.'

\echo 'EduLinks database installation completed successfully!'
\echo 'Default admin login: admin@sim.edu.az / admin123'
\echo 'Please change the admin password immediately after first login.'