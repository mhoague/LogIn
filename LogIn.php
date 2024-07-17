<?php
namespace controllers;
use LogInModel;

require_once("../models/LogInModel.php");
require_once("../helpers/sessionHelpers.php");

class LogIn
{
    private $logInModel;

    public function __construct()
    {
        $this->logInModel = new LogInModel();
    }

    public function login()
    {
        //Sanitize POST data
        $_POST = filter_input_array(INPUT_POST);

        //Init data
        $data=[
            'name/email' => trim($_POST['name/email']),
            'userPassword' => trim($_POST['userPassword'])
        ];


        if(empty($data['name/email']) || empty($data['userPassword']))
        {
            flash("login", "Please fill out all inputs");
            header("location: ../login.php");
            exit();
        }

        //Check for user/email
        if($this->userModel->findUserByEmailOrUsername($data['name/email'], $data['name/email']))
        {
            //User Found
            $loggedInUser = $this->userModel->login($data['name/email'], $data['userPassword']);
            if($loggedInUser)
            {
                //Create session
                $this->createUserSession($loggedInUser);
                var_dump($loggedInUser);
            }
            else
            {
                flash("login", "Password Incorrect");
                redirect("../login.php");
            }
        }
        else
        {
            flash("login", "No user found");
            redirect("../login.php");
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
        case 'login':
            $init->login();
            break;
        default:
            redirect("index.php");
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