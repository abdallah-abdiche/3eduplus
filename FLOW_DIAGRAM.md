# 3edu+ Authentication Flow - Visual Guide

## Page Structure (signup.php)

```
┌────────────────────────────────────────────────────────────┐
│                    AUTH CONTAINER                          │
├────────────────────────────────────────────────────────────┤
│                                                             │
│  📌 Logo & Header                                          │
│  "Bienvenue sur 3edu+"                                     │
│                                                             │
├─ ┌ Connexion │ Inscription ─────────────────────────────┬─┤
│  │                                                      │  │
│  │  ╔═════════════════════════════════════════════╗   │  │
│  │  ║       LOGIN FORM (Initially Active)         ║   │  │
│  │  ╠═════════════════════════════════════════════╣   │  │
│  │  ║ [❌ Error message if login failed]          ║   │  │
│  │  ║                                             ║   │  │
│  │  ║ Email:        [_______________]             ║   │  │
│  │  ║ Password:     [_______________]             ║   │  │
│  │  ║                                             ║   │  │
│  │  ║ ☑ Se souvenir    [Mot de passe oublié?]   ║   │  │
│  │  ║                                             ║   │  │
│  │  ║  [Se connecter]                             ║   │  │
│  │  ║                                             ║   │  │
│  │  ║  Pas encore de compte? [Créer un compte]   ║   │  │
│  │  ╚═════════════════════════════════════════════╝   │  │
│  │                                                      │  │
│  │  ╔═════════════════════════════════════════════╗   │  │
│  │  ║    SIGNUP FORM (Hidden Initially)           ║   │  │
│  │  ╠═════════════════════════════════════════════╣   │  │
│  │  ║ [✅ Success or ❌ Error message]            ║   │  │
│  │  ║                                             ║   │  │
│  │  ║ Full Name:     [_______________]            ║   │  │
│  │  ║ Email:         [_______________]            ║   │  │
│  │  ║ Password:      [_______________]            ║   │  │
│  │  ║ Wilaya:        [_______________]            ║   │  │
│  │  ║ Phone:         [_______________]            ║   │  │
│  │  ║ Date:          [_______________]            ║   │  │
│  │  ║ Image:         [Choose File]                ║   │  │
│  │  ║ Gender:        [Select ▼]                   ║   │  │
│  │  ║                                             ║   │  │
│  │  ║ ☑ J'accepte les conditions                  ║   │  │
│  │  ║                                             ║   │  │
│  │  ║  [Créer un compte]                          ║   │  │
│  │  ║                                             ║   │  │
│  │  ║  Déjà un compte? [Se connecter]             ║   │  │
│  │  ╚═════════════════════════════════════════════╝   │  │
│  │                                                      │  │
├─ └──────────────────────────────────────────────────────┘─┤
│                                                             │
│  [← Retour à l'accueil]                                    │
│                                                             │
└────────────────────────────────────────────────────────────┘
```

## User Flow Diagram

### Login Flow
```
User lands on signup.php
        ↓
[Connexion tab already active]
        ↓
User enters Email & Password
        ↓
Click [Se connecter]
        ↓
POST to signup.php → isset($_POST['signin'])
        ↓
PHP validates credentials
        ├→ Success: Set $_SESSION variables
        │  └→ redirectByRole() based on user role
        │     ├→ Admin → /dashboard/admin/
        │     ├→ Commercial → /dashboard/commercial/
        │     ├→ Pédagogique → /dashboard/pedagogique/
        │     ├→ Marketing → /dashboard/marketing/
        │     └→ Apprenant → /dashboard/apprenant/
        │
        └→ Failure: Display error message
           └→ Error persists (not auto-hidden)
```

### Signup Flow
```
User on signup.php
        ↓
Click [Inscription tab]
        ↓
[Signup form becomes visible via JavaScript]
        ↓
User fills in all fields
        ↓
Click [Créer un compte]
        ↓
POST to signup.php → isset($_POST['register'])
        ↓
PHP validates inputs
        ├→ Validation Error (missing field, invalid email, password < 6):
        │  ├→ Show error message in red box
        │  ├→ Auto-hide after 4 seconds
        │  └→ User stays on form
        │
        ├→ Duplicate Email:
        │  ├→ Show error "Email already used"
        │  ├→ Error persists (NOT auto-hidden)
        │  └→ User can correct and resubmit
        │
        └→ Success:
           ├→ Create user with role='Apprenant'
           ├→ Show success message (green box)
           ├→ User can click [Connexion] tab to login
           └→ Or provide 2-second auto-redirect (optional)
```

### Tab Switching Flow
```
User sees two tabs: [Connexion] [Inscription]

Option 1: Click Tab Button
   Click [Inscription]
      ↓
   JavaScript event listener triggers
      ↓
   Remove .active from all tabs
   Remove .active from all forms
      ↓
   Add .active to clicked tab
   Add .active to #signup-form
      ↓
   CSS shows #signup-form (display: block)
   CSS hides #login-form (display: none)

Option 2: Click Quick Link
   Click "Pas encore de compte?"
      ↓
   JavaScript finds button with data-tab="signup"
      ↓
   Simulates click on that button
      ↓
   Same as Option 1 from there
```

## Form Submission Routes

```
                   signup.php
                      ↓
         ┌─────────────┼─────────────┐
         ↓                           ↓
    $_POST['signin']         $_POST['register']
      (Login)                    (Signup)
         ↓                           ↓
   Verify credentials        Validate inputs
         ↓                           ↓
   Login success?         All valid & no duplicates?
      Yes  No                Yes    No
      ↓    ↓                 ↓      ↓
    Set  Show             Create  Show
  Session Error           User    Error
    ↓              ↓
  Redirect      Stay on
   (role)       form
```

## Error Message Handling

### Validation Errors (Auto-Hide)
- "Tous les champs requis doivent être remplis"
- "Adresse email invalide"
- "Le mot de passe doit contenir au moins 6 caractères"
- "Veuillez remplir tous les champs"

**Behavior**: Show for 4 seconds, then fade out automatically

### Business Logic Errors (Persistent)
- "Cet email est déjà utilisé. Veuillez en utiliser un autre."
- "Email ou mot de passe incorrect."
- Database errors (if any)

**Behavior**: Stay visible until user takes action

## Database Integration

### On Login Success
```php
$_SESSION['user_id'] = user_id
$_SESSION['user_email'] = Email
$_SESSION['user_name'] = Nom_Complet
$_SESSION['user_role'] = role (Apprenant|Admin|Commercial|Pédagogique|Marketing)

require_once 'auth.php';
redirectByRole($_SESSION['user_role']);
```

### On Signup Success
```
INSERT INTO utilisateurs (
  Nom_Complet,
  Mot_de_passe (bcrypt),
  Email,
  Wilaya,
  numero_tlf_utilisateur,
  date_registration,
  image_utilisateur,
  gender,
  role = 'Apprenant'
)
```

## CSS Classes Reference

| Class | Purpose |
|-------|---------|
| `.auth-form` | Form container (hidden by default) |
| `.auth-form.active` | Shows active form |
| `.tab-btn` | Tab button |
| `.tab-btn.active` | Highlights active tab |
| `.alert-message` | Error/success box |
| `.alert-error` | Red error box |
| `.alert-success` | Green success box |
| `.forgot-password` | "Forgot password?" link |
| `.switch-tab` | "Already have account?" / "Create account?" link |
| `.form-footer` | Footer with quick links |

## Testing Checklist

- [ ] Login tab loads first
- [ ] Can click Inscription tab to show signup form
- [ ] Can click Connexion tab to show login form
- [ ] Quick links work ("Pas encore de compte?" / "Déjà un compte?")
- [ ] Login with valid credentials redirects to dashboard
- [ ] Login with invalid email/password shows persistent error
- [ ] Signup validation errors auto-hide after 4 seconds
- [ ] Signup duplicate email error persists
- [ ] Signup success message displays
- [ ] All form styling matches the design
- [ ] Responsive on mobile devices
- [ ] Social buttons are visible (buttons only, no real integration yet)
