<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<form action="roles.php" method="POST">
    <div class="form-group">
        <label>Roles</label>
        <div class="input-wrapper">
            <i class="fas fa-user"></i>
            <select id="roles" name="roles" required>
                <option value="">-- Sélectionnez --</option>
                <option value="User">User</option>
                <option value="Admin">Admin</option>
                <option value="other">Other</option>
            </select>
        </div>
    </div>

<textarea name="role_description" id="role_description" placeholder="Description du rôle" required></textarea>


<input type="submit" name="submitRoles" value="Smit">

</form>
</body>
</html>



<?php
require_once 'config.php';




if(isset($_POST['submitRoles'])){
    $roles=$_POST['roles'];
    $role_description=$_POST['role_description'];
    $stmt=$conn->prepare("INSERT INTO roles (type_du_role, descriptions) VALUES (?, ?)");
    $stmt->bind_param("ss", $roles, $role_description);
    $stmt->execute();
    $stmt->close();
    header("Location: signup.html");
    exit();
}








?>