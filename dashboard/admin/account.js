
document.addEventListener("DOMContentLoaded", () => {
    const userName = document.querySelector(".user-name");

    if (!userName) return;

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
        </ul>
        <button id="close-account-window">Close Menu</button>
    `;

    document.body.appendChild(accountWindow);

    userName.addEventListener("click", (e) => {
        e.stopPropagation();

        const rect = userName.getBoundingClientRect();
        const scrollTop = window.scrollY || window.pageYOffset;

        if (accountWindow.style.display !== 'block') {
            accountWindow.style.display = "block";

            const windowWidth = window.innerWidth;
            const popupWidth = accountWindow.offsetWidth;
            const margin = 20;

            let leftPos = rect.left + rect.width / 2;

            const maxLeft = windowWidth - margin - (popupWidth / 2);

            if (leftPos > maxLeft) {
                leftPos = maxLeft;
            }

            accountWindow.style.top = (rect.bottom + 12 + scrollTop) + "px";
            accountWindow.style.left = leftPos + "px";

            requestAnimationFrame(() => {
                accountWindow.classList.add('visible');
            });
        } else {
            closeMenu();
        }
    });

    function closeMenu() {
        accountWindow.classList.remove('visible');
        setTimeout(() => {
            if (!accountWindow.classList.contains('visible')) {
                accountWindow.style.display = "none";
            }
        }, 250);
    }

    document.addEventListener("click", (e) => {
        if (!accountWindow.contains(e.target) && e.target !== userName) {
            closeMenu();
        }
    });

    document.getElementById("close-account-window").addEventListener("click", closeMenu);

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeMenu();
    });
});
