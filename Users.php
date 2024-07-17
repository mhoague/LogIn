<?php
namespace controllers;
require_once("../models/UsersModel.php");
require_once("../helpers/sessionHelpers.php");

class Users
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new \UsersModel();
    }

    public function register()
    {
        $_POST = filter_input_array(INPUT_POST);

        $data = [
            "userName" => trim($_POST["userName"]),
            "firstName" => trim($_POST["firstName"]),
            "lastName" => trim($_POST["lastName"]),
            "email" => trim($_POST["email"]),
            "aboutThis" => trim($_POST["aboutThis"]),
            "password" => trim($_POST["password"]),
            "password2" => trim($_POST["password2"]),
            "admin" => trim($_POST["admin"]),
            "built" => trim($_POST["built"]),
        ];

        //Validate inputs
        if(empty($data['userName']) || empty($data["firstName"]) || empty($data["lastName"]) || empty($data['email']) || empty($data['aboutThis']) || empty($data['password']) || empty($data['password2']))
        {
            flash("register", "Please fill out all inputs");
            redirect("../signup.php");
        }

        if(!preg_match("/^[a-zA-Z0-9]*$/", $data['userName']))
        {
            flash("register", "Invalid user name");
            redirect("../signup.php");
        }

        if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
        {
            flash("register", "Invalid email");
            redirect("../signup.php");
        }

        if(strlen($data['password']) < 6)
        {
            flash("register", "Invalid password");
            redirect("../signup.php");
        }
        else if($data['password'] !== $data['password2'])
        {
            flash("register", "Passwords don't match");
            redirect("../signup.php");
        }

        //User with the same email or password already exists
        if($this->userModel->findUserByEmailOrUsername($data['userName'], $data['email']))
        {
            flash("register", "Username or email already taken");
            redirect("../signup.php");
        }

        //Passed all validation checks.
        //Now going to hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        //Register User
        if($this->userModel->register($data))
        {
            redirect("../login.php");
        }
        else
        {
            die("Something went wrong");
        }

    }




    public function createUserSession($user)
    {
        $_SESSION['userId'] = $user->userId;
        $_SESSION['userName'] = $user->userName;
        $_SESSION['userEmail'] = $user->userEmail;
        redirect("../index.php");
    }

    public function logout()
    {
        unset($_SESSION['userId']);
        unset($_SESSION['userName']);
        unset($_SESSION['userEmail']);
        session_destroy();
        redirect("../index.php");
    }
}

$init = new Users();

//Ensure that user is sending a post request
if($_SERVER['REQUEST_METHOD'] == 'POST')
{

    switch($_POST['type'])
    {
        case 'register':
            $init->register();
            break;
        case 'login':
            $init->login();
            break;
        default:
            redirect("../index.php");
    }
}
else
{
    switch($_GET['q'])
    {
        case 'logout':
            $init->logout();
            break;
        default:
            redirect("../index.php");
    }
}
exit();