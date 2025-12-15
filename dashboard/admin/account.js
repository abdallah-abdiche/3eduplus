
document.addEventListener("DOMContentLoaded", () => {
    const userName = document.querySelector(".user-name");

    if (!userName) return;

<<<<<<< HEAD
    
    const accountWindow = document.createElement("div");
    accountWindow.id = "account-window";
    accountWindow.style.position = "absolute"; 
    accountWindow.style.backgroundColor = "#f8fafc"; 
    accountWindow.style.border = "1px solid #e5e7eb"; 
    accountWindow.style.color = "#0f172a"; 
    accountWindow.style.borderRadius = "8px";
    accountWindow.style.padding = "12px 16px";
    accountWindow.style.minWidth = "220px";
    accountWindow.style.boxShadow = "0 8px 20px rgba(2,6,23,0.08)";
    accountWindow.style.display = "none";
    accountWindow.style.zIndex = "2000";
    accountWindow.style.transition = "opacity 120ms ease, transform 120ms ease";
    accountWindow.style.opacity = "0";
    accountWindow.style.color = "";
    
   
    accountWindow.innerHTML = `
        <h3>Account Menu</h3>
        <ul style="list-style:none; padding:0; margin:0;">
            <li><a href="account/profile.html" style="text-decoration:none; color:#333;">Profile</a></li>
            <li><a href="account/settings.html" style="text-decoration:none; color:#333;">Settings</a></li>
            <li><a href="#" style="text-decoration:none; color:#333;">Logout</a></li>
=======
    // Inject styles for the account popup
    const style = document.createElement('style');
    style.innerHTML = `
        #account-window {
            position: absolute;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: #1e293b;
            border-radius: 16px;
            padding: 20px;
            min-width: 260px;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.1),
                0 4px 12px rgba(0, 0, 0, 0.05);
            display: none;
            z-index: 2000;
            opacity: 0;
            transform: translateY(-10px) translateX(-50%);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        #account-window.visible {
            opacity: 1;
            transform: translateY(0) translateX(-50%);
            display: block;
        }

        #account-window h3 {
            margin: 0 0 16px 0;
            font-size: 13px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 12px;
        }

        .account-menu-list {
            list-style: none;
            padding: 0;
  
        }

        .account-menu-list li {
            margin-bottom: 6px;
        }

        .account-menu-list a {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            text-decoration: none;
            color: #334155;
            font-size: 15px;
            font-weight: 500;
            border-radius: 12px;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .account-menu-list a:hover {
            background-color: #eff6ff;
            color: #2563eb;
            transform: translateX(4px);
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.1);
        }

        .account-menu-list a:active {
            transform: translateX(2px) scale(0.98);
        }

        #close-account-window {
            margin-top: 16px;
            width: 100%;
            padding: 10px;
            background: rgba(241, 245, 249, 0.5);
            border: 1px solid #e2e8f0;
            color: #64748b;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        #close-account-window:hover {
            background: #f1f5f9;
            color: #334155;
            border-color: #cbd5e1;
        }
    `;
    document.head.appendChild(style);

    const accountWindow = document.createElement("div");
    accountWindow.id = "account-window";

    accountWindow.innerHTML = `
        <h3>My Account</h3>
        <ul class="account-menu-list">
            <li><a href="profile.php">Profile</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="../../logout.php" style="color: #ef4444;">Logout</a></li>
>>>>>>> 3e34b36 (newe version)
        </ul>
        <button id="close-account-window">Close Menu</button>
    `;

    document.body.appendChild(accountWindow);

    userName.addEventListener("click", (e) => {
        e.stopPropagation();

<<<<<<< HEAD
    
        const rect = userName.getBoundingClientRect();
        const scrollTop = window.scrollY || window.pageYOffset;

       
        accountWindow.style.display = "block";
        accountWindow.style.top = (rect.bottom + 8 + scrollTop) + "px";
        accountWindow.style.left = (rect.left + rect.width / 2) + "px";
        accountWindow.style.transform = "translateX(-50%)";
                                                                                 
        requestAnimationFrame(() => {
            accountWindow.style.opacity = "1";
        });
    });
=======
        const rect = userName.getBoundingClientRect();
        const scrollTop = window.scrollY || window.pageYOffset;

        // Reset visibility to allow display:block to take effect before opacity transition
        if (accountWindow.style.display !== 'block') {
            accountWindow.style.display = "block";

            // Calculate position
            const windowWidth = window.innerWidth;
            const popupWidth = accountWindow.offsetWidth; // Now we can measure it
            const margin = 20; // ~1em spacing

            // Initial centered position
            let leftPos = rect.left + rect.width / 2;
>>>>>>> 3e34b36 (newe version)

            // Check right boundary
            // The right edge of the popup would be at: leftPos + (popupWidth / 2)
            // We want: leftPos + (popupWidth / 2) <= windowWidth - margin
            const maxLeft = windowWidth - margin - (popupWidth / 2);

<<<<<<< HEAD
    document.addEventListener("click", (e) => {
        if (!accountWindow.contains(e.target) && e.target !== userName) {
            accountWindow.style.opacity = "0";
   
            setTimeout(() => accountWindow.style.display = "none", 150);
        }
    });

   
    document.getElementById("close-account-window").addEventListener("click", () => {
        accountWindow.style.display = "none";
    });

    
=======
            if (leftPos > maxLeft) {
                leftPos = maxLeft;
            }

            // Apply calculated position
            accountWindow.style.top = (rect.bottom + 12 + scrollTop) + "px";
            accountWindow.style.left = leftPos + "px";

            // Allow browser reflow
            requestAnimationFrame(() => {
                accountWindow.classList.add('visible');
            });
        } else {
            // Already open? close it
            closeMenu();
        }
    });

    function closeMenu() {
        accountWindow.classList.remove('visible');
        setTimeout(() => {
            if (!accountWindow.classList.contains('visible')) {
                accountWindow.style.display = "none";
            }
        }, 250); // Match transition time
    }

    document.addEventListener("click", (e) => {
        if (!accountWindow.contains(e.target) && e.target !== userName) {
            closeMenu();
        }
    });

    document.getElementById("close-account-window").addEventListener("click", closeMenu);

>>>>>>> 3e34b36 (newe version)
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeMenu();
    });
});
