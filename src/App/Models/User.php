<?php

namespace App\Models;

use Core\Model;
use Helpers\FormField;

class User extends Model
{
    protected string $table = 'wishlist_users';
    public string $username, $fullname, $name, $email;
    public bool $admin, $email_verified, $dark_theme;

    public function checkIfLoggedIn(): bool
    {
        // Check if the user is authenticated
        $logged_in = $_SESSION["wishlist_logged_in"] ?? false;
        if(!$logged_in){
            if(isset($_COOKIE["wishlist_session_id"])){
                $session = $_COOKIE["wishlist_session_id"];
                $query = "SELECT username, session_expiration FROM $this->table WHERE session = ?";
                $findUser = $this->select(query: $query, values: [$session]);
                if(count($findUser) > 0){
                    foreach($findUser as $row){
                        $session_expiration = $row["session_expiration"];
                        if(date("Y-m-d H:i:s") < $session_expiration){
                            $username = $row["username"];
                            $_SESSION["username"] = $username;
                            $logged_in = true;
                            $_SESSION["wishlist_logged_in"] = true;
                        }
                    }
                }
            }
        }
        
        return $logged_in;
    }

    public function setInformation(): void
    {
        // get username
        $username = $_SESSION["username"] ?? "";

        // find name based off of username
        $findUserInfo = $this->select("SELECT name, role, email, unverified_email, dark FROM $this->table WHERE username = ?", [$username]);
        if(count($findUserInfo) > 0){
            foreach($findUserInfo as $row){
                $this->name = htmlspecialchars($row["name"]);
                $_SESSION["name"] = $this->name;
                $this->admin = $row["role"] == "Admin" ? true : false;
                $this->fullname = $row["name"];
                $this->email = $row["email"];
                $this->email_verified = $row["unverified_email"] == "" ? true : false;
                $this->dark_theme = $row["dark"] == "Yes" ? true : false;
            }
        }
    }

    public function validateUser(FormField $username, FormField $password): bool
    {
        $findUser = $this->select("SELECT username, password FROM $this->table WHERE username = ? OR email = ?", [$username->value, $username->value]);

        if(count($findUser) > 0){
            foreach($findUser as $row){
                $hashed_password = $row["password"];
                $username = $row["username"];
                return password_verify($password->value, $hashed_password);
            }
        }else{
            return false;
        }
        return false;
    }

    public function setRememberMeSession(FormField $username, FormField $rememberMe): bool
    {
        if($rememberMe->value == "Yes"){
            $expire_date = date("Y-m-d H:i:s", strtotime("+1 year"));
            $sql = "UPDATE $this->table SET session = ?, session_expiration = ? WHERE username = ?";
            $values = [session_id(), $expire_date, $username->value];
        }else{
            $sql = "UPDATE $this->table SET session = NULL, session_expiration = NULL WHERE username = ?";
            $values = [$username->value];
        }
        if($this->write($sql, $values)){
            if($rememberMe->value == "Yes"){
                $cookie_time = (3600 * 24 * 365); // 1 year
                setcookie("wishlist_session_id", session_id(), time() + $cookie_time);
            }
            return true;
        }else{
            return false;
        }
    }

    public function changeTheme(): void
    {
        header('Content-Type: application/json'); // Set header for JSON response

        $response = ['status' => 'error', 'message' => 'Unknown error'];

        if (isset($_SESSION["username"])) {
            $username = $_SESSION["username"];
            
            if (isset($_POST["dark"])) {
                // Update the dark mode setting in the database
                $this->write("UPDATE wishlist_users SET dark = ? WHERE username = ?", [$_POST["dark"], $username]);

                // Successful update
                $response = [
                    'status' => 'success',
                    'message' => 'Theme updated successfully',
                    'dark' => $_POST["dark"] // Include the updated dark mode status
                ];
            } else {
                $response['message'] = 'No dark mode setting provided';
            }
        } else {
            $response['message'] = 'User not logged in';
        }

        // Return the JSON response
        echo json_encode($response);

    }
}

?>