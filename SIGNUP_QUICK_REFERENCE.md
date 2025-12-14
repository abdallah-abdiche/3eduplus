# Quick Reference - signup.php

## File Location
`c:\xampp\htdocs\3eduplus\signup.php`

## Access
http://localhost/3eduplus/signup.php

## What It Does
Combines both login and signup in one file with tab switching between forms.

## Tab Switching

### Two Tabs Available
1. **Connexion** (Login) - Active by default
2. **Inscription** (Signup) - Toggle to this tab

### Switch Methods
- Click the tab button at the top
- Click quick links at bottom of each form:
  - "Pas encore de compte?" (in login form) → goes to signup
  - "Déjà un compte?" (in signup form) → goes to login

## Login Form Fields
- Email (required)
- Password (required)
- "Remember me" checkbox (optional)
- "Forgot password?" link (currently non-functional)
- Social login buttons (Google, Facebook) - placeholder only

## Signup Form Fields
- Full Name (required)
- Email (required, must be valid format, must be unique)
- Password (required, minimum 6 characters)
- Wilaya (required)
- Phone Number (required)
- Registration Date (required)
- Profile Image (optional)
- Gender (required)
- Terms & Conditions checkbox (required)
- Social login buttons (Google, Facebook) - placeholder only

## Form Processing

### POST Variables
**Login:**
- `name="signin"` - Submit button name
- `login_email` - Email input
- `login_password` - Password input

**Signup:**
- `name="register"` - Submit button name
- `name` - Full name
- `email` - Email
- `password` - Password
- `wilaya` - Wilaya
- `numero_tlf_utilisateur` - Phone
- `date_registration` - Date
- `image_utilisateur` - Image file
- `gender` - Gender
- `terms` - Checkbox for accepting terms

### Response Handling
Both forms POST to `signup.php` itself, so response appears on same page.

## Error Messages

### Displays in Red Alert Box (❌)

#### Auto-Hide (4 seconds):
- "Tous les champs requis doivent être remplis"
- "Adresse email invalide"
- "Le mot de passe doit contenir au moins 6 caractères"
- "Veuillez remplir tous les champs"

#### Persistent (stays visible):
- "Cet email est déjà utilisé. Veuillez en utiliser un autre."
- "Email ou mot de passe incorrect."

## Success Messages

### Displays in Green Alert Box (✅)
- "Compte créé avec succès!" (signup)
- Message appears above the form

## Authentication After Login

When login succeeds:
1. Session variables set:
   - `$_SESSION['user_id']`
   - `$_SESSION['user_email']`
   - `$_SESSION['user_name']`
   - `$_SESSION['user_role']`

2. Auto-redirect to dashboard based on role:
   - Apprenant → `/dashboard/apprenant/`
   - Admin → `/dashboard/admin/`
   - Commercial → `/dashboard/commercial/`
   - Pédagogique → `/dashboard/pedagogique/`
   - Marketing → `/dashboard/marketing/`

## Password Hashing
- Uses `password_hash()` with DEFAULT algorithm
- Verification with `password_verify()`
- Secure against common attacks

## Database Lookups
- **Login**: Queries `utilisateurs` table for Email
- **Signup**: 
  1. Checks if Email already exists
  2. Inserts new user if email is unique
  3. Default role is always "Apprenant"

## Styling Classes
All CSS is in `signup.css`:
- `.auth-form` - Form container
- `.auth-form.active` - Shows/hides forms
- `.tab-btn` - Tab button
- `.tab-btn.active` - Active tab highlight
- `.alert-message` - Error/success box
- `.alert-error` - Red background
- `.alert-success` - Green background

## Dependencies
- `config.php` - Database connection
- `auth.php` - Authentication helper functions (for redirectByRole)
- `signup.css` - Styling
- Font Awesome - Icons (CDN)

## No Longer Needed
- `login.php` - Its functionality is now in signup.php
- Can optionally delete the old login.php file

## Important Notes

1. **Both forms use POST to same file**
   - PHP checks for `isset($_POST['signin'])` or `isset($_POST['register'])`
   - This determines which form was submitted

2. **JavaScript controls visibility**
   - CSS classes `.active` show/hide forms
   - JavaScript listeners handle tab clicks
   - No page reload during tab switching

3. **Error handling is smart**
   - Validation errors disappear automatically (user won't miss them if they wait)
   - Important errors (duplicate email) stay visible (forces user action)

4. **Mobile responsive**
   - All styles adapt to mobile/tablet
   - Touch-friendly buttons and inputs

## Testing
```
1. Load signup.php
   → Login tab should be active

2. Click signup tab
   → Signup form appears

3. Fill signup form & submit
   → See success message
   → Or error if validation fails

4. Click login tab
   → Signup form disappears

5. Login with valid credentials
   → Redirect to dashboard

6. Logout (if dashboard has logout button)
   → Redirect to index.html
```

## Future Enhancements
- [ ] Real password reset functionality
- [ ] Real social login (Google, Facebook OAuth)
- [ ] Email verification
- [ ] Two-factor authentication
- [ ] Session timeout handling
- [ ] Remember me cookie implementation
