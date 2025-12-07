<?php 

session_start();
require_once 'config.php';
require_once 'signup.html';

if (isset($_POST['register'])) {
    $username = $_POST['name'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $wilaya   = $_POST['wilaya'];
    $numero   = $_POST['numero_tlf_utilisateur'];
    $date     = $_POST['date_registration'];
    $image    = $_POST['image_utilisateur'];
    $gender   = $_POST['gender'];

    $checkEmail = $conn->query("SELECT email FROM utilisateurs WHERE email = '$email'");

    if ($checkEmail->num_rows > 0) {
        $_SESSION['register-error'] = 'Email already exists';
        $_SESSION['active_form'] = 'register';
        header("Location: signup.html");
        exit();
    } else {
        $stmt = $conn->prepare("INSERT INTO utilisateurs (nom_Complet, mot_de_passe,email, wilaya, numero_tel, date_registration, image_utilisateur, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("ssssisss", $username, $password, $email, $wilaya, $numero, $date, $image, $gender);
        $stmt->execute();
        $stmt->close();

        
        unset($_SESSION['register-error'], $_SESSION['active_form']);
        header("Location: login.html");
        exit();
    }
}


if (isset($_POST['login'])) {
    $email= $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['mot_de_passe'])) {
          
           

            unset($_SESSION['login-error'], $_SESSION['active_form']);
            header("Location: dashboard.html");
            exit();
        } else {
            $_SESSION['login-error'] = 'Invalid email or password';
            $_SESSION['active_form'] = 'login';
            header("Location: login.html");
            exit();
        }
    } else {
        $_SESSION['login-error'] = 'Invalid email or password';
        $_SESSION['active_form'] = 'login';
        header("Location: login.html");
        exit();
    }
}   
?>
