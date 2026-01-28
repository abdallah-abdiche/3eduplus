# Admin Inscriptions Page - Fix Documentation

## Problem Fixed

The admin inscriptions page (`dashboard/admin/purchased-courses.php`) had a critical issue where it was trying to query the `inscriptions` table using a `formation_id` column that might not exist yet in your database. This would cause SQL errors or blank pages.

## Solution Implemented

### 1. **Database Column Check**

The page now checks if the `formation_id` column exists before trying to use it:

```php
$check_column_query = "SHOW COLUMNS FROM inscriptions LIKE 'formation_id'";
$column_check = $conn->query($check_column_query);
$has_formation_id = ($column_check && $column_check->num_rows > 0);
```

### 2. **Multiple Data Sources**

The page now fetches inscriptions from two sources:

- **Purchases** (`paiement_formations` table) - Always queried
- **Inscriptions** (`inscriptions` table) - Only queried if `formation_id` column exists

### 3. **Error Handling**

- Added try-catch blocks to prevent crashes
- Displays user-friendly error messages if something goes wrong
- Shows a warning banner if the database structure is incomplete

### 4. **Security Improvements**

- All user data is now properly escaped using `htmlspecialchars()`
- Added null coalescing operators (`??`) to prevent undefined index errors

### 5. **Better User Interface**

- Added a "Source" column to show where each inscription came from (Purchases or Inscriptions)
- Warning banner with a direct link to fix the database if needed
- Better styling for error and warning messages

## How to Use

### Accessing the Page

1. Login as an Admin
2. Navigate to **Dashboard > Inscriptions** from the sidebar

### If You See a Warning

If you see a yellow warning banner saying "The 'formation_id' column is missing", you should:

1. Click the "Click here to fix it" link in the warning
2. Or manually visit: `http://localhost/3eduplus/fix_inscriptions_table.php`
3. Follow the instructions on that page to add the missing column

### What You'll See

The inscriptions table will show:

- **ID**: Unique inscription/purchase ID
- **Student**: Full name of the enrolled student
- **Email**: Student's email address
- **Course**: Name/title of the formation
- **Price**: Course price in DA (Algerian Dinar)
- **Date**: When the inscription/purchase was made
- **Source**: Badge showing "Purchases" (green) or "Inscriptions" (blue)

## Database Structure Required

For full functionality, your `inscriptions` table should have:

- `inscription_id` (Primary Key)
- `user_id` (Foreign Key to utilisateurs)
- `formation_id` (Foreign Key to formations) ← **This was missing**
- `date_inscription` (Timestamp)

The `fix_inscriptions_table.php` script will automatically add the missing `formation_id` column if needed.

## Technical Notes

### Before Fix:

- Page would crash if `formation_id` column didn't exist
- No error handling
- Data not properly escaped (security risk)
- Only attempted to get data from one source

### After Fix:

- Gracefully handles missing columns
- Fetches data from multiple sources
- Proper error handling and user feedback
- Security improvements with `htmlspecialchars()`
- Better UX with warnings and fix suggestions

## Testing

To test if the fix is working:

1. Visit the inscriptions page as an admin
2. Check if you see any inscriptions (from purchases or inscriptions table)
3. If you see a warning banner, run the fix script
4. Verify that new inscriptions appear after students purchase courses

## Support

If you still have issues:

1. Check your PHP error logs
2. Verify that XAMPP/Apache is running
3. Ensure the database connection is working (`config.php`)
4. Make sure you have admin privileges in the system
