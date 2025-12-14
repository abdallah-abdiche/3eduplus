# 🔧 Database Setup Required

Your database is missing the required columns and tables. Follow these steps:

## Option 1: Automatic Setup (Recommended)

1. **Go to**: http://localhost/3eduplus/setup_database.php
2. **Wait** for the setup script to complete
3. You'll see a "Setup Complete!" message
4. You're done! The database is ready

## Option 2: Manual phpMyAdmin Setup

1. **Open phpMyAdmin**: http://localhost/phpmyadmin
2. **Select your database** (3eduplus or whatever it's called)
3. **Click SQL tab**
4. **Copy and paste** all SQL from `database_schema.sql`
5. **Execute** (Ctrl+Enter)

## What Gets Added

The setup script adds:
- ✅ `role` column to `utilisateurs` table
- ✅ `formations` table (courses)
- ✅ `inscriptions` table (enrollments)
- ✅ `panier` table (shopping cart)
- ✅ `paiements` table (payments)
- ✅ `recus` table (receipts)
- ✅ `paiement_formations` table (payment-course mapping)

## After Setup

Once complete, you can:
1. Go to `signup.php` to register and login
2. Go to `formation.php` to browse courses
3. Add courses to cart and checkout
4. View receipts

## Troubleshooting

### Error: "Connection failed"
- Check `config.php` has correct database credentials
- Verify MySQL is running in XAMPP
- Check database name exists

### Error: "Unknown column"
- Run setup_database.php again
- Or manually run the SQL from database_schema.sql

### Error: "Table already exists"
- That's fine! The tables already exist, no action needed
- The setup script will skip them

## Questions?

Check these files:
- `config.php` - Database connection settings
- `database_schema.sql` - SQL to create all tables
- `setup_database.php` - Automatic setup script
