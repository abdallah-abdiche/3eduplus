        function showTab(tab) {
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(t => t.classList.remove('active'));
            
            if (tab === 'settings') {
                document.getElementById('settingsTab').style.display = 'block';
                document.getElementById('experienceTab').classList.remove('active');
                tabs[0].classList.add('active');
            } else {
                document.getElementById('settingsTab').style.display = 'none';
                document.getElementById('experienceTab').classList.add('active');
                tabs[1].classList.add('active');
            }
        }

        function saveProfile(event) {
            event.preventDefault();
            
            const firstName = document.getElementById('firstName').value;
            const surname = document.getElementById('surname').value;
            const phone = document.getElementById('phone').value;
            const address = document.getElementById('address').value;
            const email = document.getElementById('email').value;
            const education = document.getElementById('education').value;
            const country = document.getElementById('country').value;
            const state = document.getElementById('state').value;

            const profile = {
                firstName,
                surname,
                phone,
                address,
                email,
                education,
                country,
                state
            };

            console.log('Profile saved:', profile);
            alert('Profile saved successfully!');
        }

        function saveExperience(event) {
            event.preventDefault();
            
            const experience = document.getElementById('experienceDesign').value;
            const additionalDetails = document.getElementById('additionalDetails').value;

            const experienceData = {
                experience,
                additionalDetails
            };

            console.log('Experience saved:', experienceData);
            alert('Experience saved successfully!');
        }