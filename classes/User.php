<?php

    # INCLUDE - is not so strict, it will always look for the database.php if it is included or not. If the "Database.php" is not included, the program will throw an error message, but it will run the program.

    # REQUIRE_ONCE - it is stricter compared to "include". It make sure that the "Database.php" is added before running the entire program. Meaning, it the "Database.php" is not found, the program will throw an error and will never run.

# The logic of our application will live in this file.
    require_once "Database.php";

    class User extends Database{

        public function store($request){
            $first_name = $request['first_name'];
            $last_name = $request['last_name'];
            $username = $request['username'];
            $password = $request['password'];

            # Hash the password
            # admin12345 ---> 245asdfeqkuy26393s
            $password = password_hash($password, PASSWORD_DEFAULT);
            #Note: $password --> original password coming from form
            # PASSWORD_DEFAULT --> algorithm use in PHP to hash the password

            # SQL query string
            $sql = "INSERT INTO users(`first_name`, `last_name`, `username`, `password`) VALUES('$first_name', '$last_name', '$username', '$password')";

            # execute the query
            if ($this->conn->query($sql)) {
                header('location: ../views'); //go to index.php (login page) 
                exit;                        //same as die() function
            }else {
                die("Error in creating the user: " . $this->conn->error);
            }
        }

        public function login($request){
            $username = $request['username'];
            $password = $request['password'];

            # SQL query string
            $sql = "SELECT * FROM users WHERE username = '$username'";

            $result = $this->conn->query($sql);

            # Check if the username exists
            if ($result->num_rows == 1) {
                # Check if the password is correct
                $user = $result->fetch_assoc();
                # $user = ['id' => 1, 'usernamne' => 'john', 'password' => '$38R3ds309(*67', ...]

                # Verify if the password match with the password in the database
                if (password_verify($password, $user['password'])) {
                    # Create a session variable
                    session_start();
                    $_SESSION['id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['first_name'] . " " . $user['last_name'];
                    
                    header('location: ../views/dashboard.php'); // we'll create the dashboard.php later on
                    exit;
                }else {
                    die("Password does not match");
                }
            }else {
                die("Username not found.");
            }
        }

        public function logout(){
            # unset and removed the session variables from login method
            session_start();
            session_unset();
            session_destroy();

            header('location: ../views'); //redirect the user to the login page
            exit;
        }

        # retreived all the users from the database
        public function getAllUsers(){
            $sql = "SELECT id, first_name, last_name, username, photo FROM users";

            if ($result = $this->conn->query($sql)) {
                return $result;
            }else {
                die("Error in retrieving the users. " . $this->conn->error);
            }
        }

        # retrieved specific user from the database to make edit
        # Note: the $id = refers to the id of the user who are logged-in
        public function getUser($id){
            $sql  = "SELECT * FROM users WHERE id = $id";

            if ($result = $this->conn->query($sql)) {
                return $result->fetch_assoc();
            }else {
                die('Error in retrieving the user details. ' . $this->conn->error);
            }
        }

        public function update($request, $files){
            session_start();
            $id = $_SESSION['id'];
            $first_name = $request['first_name'];
            $last_name = $request['last_name'];
            $username = $request['username'];

            # Photo/Image uploaded
            $photo = $files['photo']['name']; //The 'photo' is the name of the field we get the uploaded file coming from the form, while the 'name' is the name of the uplaoded file
            $tmp_photo = $files['photo']['tmp_name']; //the 'tmp_name' is a temporary storage inside our computer memory

            # Sql query string
            $sql = "UPDATE users SET first_name = '$first_name', last_name = '$last_name', username = '$username' WHERE id = $id";

            # Execute the query
            if ($this->conn->query($sql)) {
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = "$first_name $last_name";

                # If there is an uploaded photo, save it to the db and save the file into the images folder
                if ($photo) { // check if there is a photo uploaded -- true or false?
                    $sql = "UPDATE users SET photo = '$photo' WHERE id = $id";
                    $destination = "../assets/images/$photo";

                    # Save the image to the db
                    if ($this->conn->query($sql)) {
                        # Same the image to the images forlder
                        if (move_uploaded_file($tmp_photo, $destination)){
                            header('location: ../views/dashboard.php');
                            exit;
                        }else{
                            die("Error in moving the photo.");
                        }
                    }else{
                        die("Error in uploading the photo.");
                    }
                }
                header('location: ../views/dashboard.php');
                exit;
            }else {
                
                die("Error in updating the user. " . $this->conn->error);
            }
            
        }

        public function delete(){
            session_start();
            $id=$_SESSION['id'];

            $sql="DELETE FROM users WHERE id=$id";

            if($this->conn->query($sql)){
                $this->logout();
            }else{
                die("Error in deleting your accounts".$this->conn->error);
            }
        }

        public function testOnly(){
            die("This is a test");
        }
    }