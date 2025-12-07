
document.addEventListener("DOMContentLoaded", () => {
    const userName = document.querySelector(".user-name");

    if (!userName) return;

    
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
        </ul>
        <button id="close-account-window" style="margin-top:15px;padding:5px 10px;">Close</button>
    `;

    document.body.appendChild(accountWindow);


    userName.addEventListener("click", (e) => {
        e.stopPropagation();

    
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


    document.addEventListener("click", (e) => {
        if (!accountWindow.contains(e.target) && e.target !== userName) {
            accountWindow.style.opacity = "0";
   
            setTimeout(() => accountWindow.style.display = "none", 150);
        }
    });

   
    document.getElementById("close-account-window").addEventListener("click", () => {
        accountWindow.style.display = "none";
    });

    
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") accountWindow.style.display = "none";
    });
});
