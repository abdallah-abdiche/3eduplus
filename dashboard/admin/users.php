                        <!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>Dashboard - 3edu+</title>
                            <link rel="stylesheet" href="style.css">
                            <link rel="stylesheet" href="CRUD.css">
                            <link rel="icon" href="../LogoEdu.png" type="image/png">
                            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
                            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                        </head>
                        <body>
                            <div class="dashboard-container">
                            
                                <aside class="sidebar">
                                    <div class="sidebar-header">
                                        <div class="logo">
                                            <img src="../LogoEdu.png" alt="3edu+ Logo">
                                        
                                        </div>
                                    </div>

                                    <nav class="sidebar-nav">
                                        <div class="nav-section">
                                            <p class="nav-section-title">MENU</p>
                                            <ul class="nav-menu">
                                                <li class="nav-item ">
                                                    <a href="index.html" class="nav-link">
                                                        <i class="fas fa-chevron-up nav-arrow"></i>
                                                        <span>Dashboard</span>
                                                    </a>
                                                
                                                </li>
                                                <li class="nav-item active">
                                                    <a href="users.html" class="nav-link">
                                                        <i class="fas fa-chevron-down nav-arrow"></i>
                                                        <span>Users</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="courses.html" class="nav-link">
                                                        <i class="fas fa-chevron-down nav-arrow"></i>
                                                        <span>Courses</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="reports.html" class="nav-link">
                                                        <i class="fas fa-chevron-down nav-arrow"></i>
                                                        <span>Reports</span>
                                                    </a>
                                                </li>
                                            
                                            </ul>
                                        </div>

                                        <div class="nav-section">
                                            <p class="nav-section-title">OTHERS</p>
                                            <ul class="nav-menu">
                                                <li class="nav-item">
                                                    <a href="#" class="nav-link">
                                                        <i class="fas fa-chevron-down nav-arrow"></i>
                                                        <span>Charts</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#" class="nav-link">
                                                        <i class="fas fa-chevron-down nav-arrow"></i>
                                                        <span>UI Elements</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#" class="nav-link">
                                                        <i class="fas fa-chevron-down nav-arrow"></i>
                                                        <span>Authentication</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </nav>


                                </aside>

                            
                                <main class="main-content">
                                    
                                    <header class="dashboard-header">
                                        <div class="header-left">
                                            <h1 class="header-logo">Dashboard</h1>
                                        </div>
                                        <div class="header-center">
                                            <div class="search-box">
                                                <i class="fas fa-search"></i>
                                                <input type="text" placeholder="Search or type command..." class="search-input">
                                                <span class="search-shortcut">K</span>
                                            </div>
                                        </div>
                                        <div class="header-right">
                                            <button class="header-icon-btn dark-mode-toggle" title="Dark Mode">
                                                <i class="fas fa-moon"></i>
                                            </button>
                                            <button class="header-icon-btn notifications-btn" title="Notifications">
                                                <i class="fas fa-bell"></i>
                                                <span class="notification-badge">3</span>
                                            </button>
                                            <div class="user-profile">
                                                <img src="https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fstatic.vecteezy.com%2Fsystem%2Fresources%2Fpreviews%2F000%2F290%2F610%2Foriginal%2Fadministration-vector-icon.jpg&f=1&nofb=1&ipt=0c0a886cbda8307543dc1e414a300f5a4d50a9c8884b6fd80567d4bf75248a31" class="admin-avatar">
                                                <span class="user-name">Account</span>
                                                <i class="fas fa-chevron-down"></i>

                                            </d </div>
                                    </header>

                                
                                    <div class="dashboard-content">
                                    
                                        <h2>Users Management</h2>
                                        <p>Manage all registered users from this dashboard.</p>
                                    <table width="100%" border="1" class="users-table"  cellpadding="10" cellspacing="0">
        <tr>
            <th>User ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Wilaya</th>
            <th>Phone Number</th>
            <th>Registration Date</th>
            <th>Gender</th>
            <th>Action</th>
        </tr>
                                    
                                    
                                    <?php 
                                    $username="root";
                                    $password = "";
                                    $database = "3eduplus";
                                    $servername = "localhost";
                                    $conn = new mysqli($servername, $username, $password, $database);

                                    if ($conn->connect_error) {
                                        die("Connection failed: " . $conn->connect_error);
                                        }
                                        $sql = "SELECT * FROM utilisateurs";
                                        $result = $conn->query($sql);
                                        if(!$result){
                                            die("Invalid query: " . $conn->error);
                                        }

                while($row = $result->fetch_assoc()){
                    echo "
                    <tr>
                        <td >{$row['user_id']}</td>
                        <td>{$row['Nom_Complet']}</td>
                        <td>{$row['Email']}</td>
                        <td>{$row['Wilaya']}</td>
                        <td>{$row['numero_tlf_utilisateur']}</td>
                        <td>{$row['date_registration']}</td>
                        <td>{$row['gender']}</td>
                        <td>

                            <a href='edit_users.php?user_id={$row['user_id']}' class='btn btn-primary btn-sm'>Edit</a>
                            <a href='delete_user.php?id={$row['user_id']}' class='btn btn-danger btn-sm'>Delete</a>
                        </td>
                    </tr>
                    ";
                }

              
                
                    ?>   
                    </table>



                                    </div>
                                </main>
                            </div>

                            

                            <script src="account.js"></script>
                        </body>
                        </html>

