        
        const navButtons = document.querySelectorAll('.nav-btn');
        const sections = document.querySelectorAll('.settings-section');

        navButtons.forEach(button => {
            button.addEventListener('click', () => {
                const targetSection = button.getAttribute('data-section');
                
                
                navButtons.forEach(btn => btn.classList.remove('active'));
                sections.forEach(section => section.classList.remove('active'));
                
                button.classList.add('active');
                document.getElementById(targetSection).classList.add('active');
            });
        });

        
        document.getElementById('profileForm').addEventListener('submit', (e) => {
            e.preventDefault();
            saveSettings('profile', new FormData(e.target));
        });

        document.getElementById('accountForm').addEventListener('submit', (e) => {
            e.preventDefault();
            saveSettings('account', new FormData(e.target));
        });

        document.getElementById('privacyForm').addEventListener('submit', (e) => {
            e.preventDefault();
            saveSettings('privacy', new FormData(e.target));
        });

        document.getElementById('notificationsForm').addEventListener('submit', (e) => {
            e.preventDefault();
            saveSettings('notifications', new FormData(e.target));
        });

        // Avatar upload preview
        document.getElementById('avatar').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    document.getElementById('avatarImg').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Save settings function (AJAX call to PHP backend)
        function saveSettings(section, formData) {
            // Add section identifier
            formData.append('section', section);
            
            // Show loading state (you can add a spinner here)
            const submitBtn = document.querySelector(`#${section}Form button[type="submit"]`);
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Saving...';
            submitBtn.disabled = true;

            // AJAX request to PHP backend
            fetch('settings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Settings saved successfully!', 'success');
                } else {
                    showAlert(data.message || 'Error saving settings', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error saving settings. Please try again.', 'error');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        }

        // Show alert messages
        function showAlert(message, type) {
            const alert = document.getElementById('alert');
            alert.textContent = message;
            alert.className = `alert ${type}`;
            alert.style.display = 'block';
            
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        }

        // Reset form function
        function resetForm(formId) {
            document.getElementById(formId).reset();
            showAlert('Form reset to original values', 'success');
        }

        // Export data function
        function exportData() {
            if (confirm('Are you sure you want to export your data? This will download all your account information.')) {
                // Call PHP endpoint to export data
                window.location.href = 'export-data.php';
            }
        }

        // Delete account function
        function deleteAccount() {
            if (confirm('Are you absolutely sure? This will permanently delete your account and all associated data. This action cannot be undone.')) {
                const password = prompt('Please enter your password to confirm account deletion:');
                if (password) {
                    // Send delete request to PHP backend
                    fetch('delete-account.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ password: password })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Account deleted successfully. Redirecting to homepage...');
                            window.location.href = '/';
                        } else {
                            alert('Error deleting account: ' + (data.message || 'Please try again.'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error deleting account. Please try again.');
                    });
                }
            }
        }

        // Load existing settings on page load
        window.addEventListener('DOMContentLoaded', () => {
            // Fetch current settings from PHP backend
            fetch('settings.php?action=get')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.settings) {
                        // Populate forms with existing data
                        populateForms(data.settings);
                    }
                })
                .catch(error => {
                    console.error('Error loading settings:', error);
                });
        });

        // Populate forms with existing data
        function populateForms(settings) {
            // Profile settings
            if (settings.profile) {
                document.getElementById('fullName').value = settings.profile.fullName || '';
                document.getElementById('username').value = settings.profile.username || '';
                document.getElementById('email').value = settings.profile.email || '';
                document.getElementById('bio').value = settings.profile.bio || '';
                document.getElementById('timezone').value = settings.profile.timezone || 'UTC';
                if (settings.profile.avatar) {
                    document.getElementById('avatarImg').src = settings.profile.avatar;
                }
            }

            // Account settings
            if (settings.account) {
                document.getElementById('language').value = settings.account.language || 'en';
                document.getElementById('twoFactor').checked = settings.account.twoFactor || false;
            }

            // Privacy settings
            if (settings.privacy) {
                document.getElementById('profileVisible').checked = settings.privacy.profileVisible !== false;
                document.getElementById('showEmail').checked = settings.privacy.showEmail || false;
                document.getElementById('allowMessages').checked = settings.privacy.allowMessages !== false;
                document.getElementById('dataCollection').checked = settings.privacy.dataCollection !== false;
                document.getElementById('searchVisibility').value = settings.privacy.searchVisibility || 'public';
            }

            // Notification settings
            if (settings.notifications) {
                document.getElementById('emailNewsletter').checked = settings.notifications.emailNewsletter !== false;
                document.getElementById('emailMessages').checked = settings.notifications.emailMessages !== false;
                document.getElementById('emailMentions').checked = settings.notifications.emailMentions !== false;
                document.getElementById('pushMessages').checked = settings.notifications.pushMessages !== false;
                document.getElementById('pushUpdates').checked = settings.notifications.pushUpdates || false;
                document.getElementById('notificationFrequency').value = settings.notifications.notificationFrequency || 'instant';
            }
        }