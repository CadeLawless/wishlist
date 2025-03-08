<?php

namespace App\Controllers;

use Core\Controller;
use Helpers\FormValidation;
use Helpers\FormField;
use App\Models\User;

class LoginController extends Controller
{
    private formValidation $formValidation;
    private FormField $username;
    private FormField $password;
    private FormField $rememberMe;

    public function __construct(User|null $user)
    {
        parent::__construct($user);
        $this->formValidation = new FormValidation();
        $this->username = new FormField(
            formValidation: $this->formValidation,
            name: "username",
            type: "text",
            required: true,
            label: "Username or Email"
        );
        $this->password = new FormField(
            formValidation: $this->formValidation,
            name: "password",
            type: "password",
            required: true,
            label: "Password"
        );
        $this->rememberMe = new FormField(
            formValidation: $this->formValidation,
            name: "remember_me",
            type: "checkbox",
            required: false,
            label:"Remember Me"
        );
    }

    public function showForm(): void
    {
        $this->view('login', ['title' => 'Wish List | Login', 'formValidation' => $this->formValidation]);
    }

    public function handleForm(): void
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->formValidation->validateFormFields();

            $user = new User();
            if($user->validateUser(username: $this->username, password: $this->password)){
                if($user->setRememberMeSession(username: $this->username, rememberMe: $this->rememberMe)){
                    $_SESSION["wishlist_logged_in"] = true;
                    $_SESSION["username"] = $this->username->value;
                    header('Location: /wishlist1/');
                }else{
                    $this->rememberMe->setErrors(message: "Something went wrong while trying to log you in");
                }
            }else{
                $this->username->setErrors(message: "Username/email or password is incorrect");
            }
            $this->showForm();
        }
    }
}

?>