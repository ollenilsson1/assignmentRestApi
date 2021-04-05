<?php

class UserException extends Exception
{}

class User
{
    private $_userid;
    private $_fullname;
    private $_username;
    private $_email;
    private $_role;
    private $_createdAt;
    private $_useractive;
    private $_loginattempts;

    public function __construct($userid, $fullname, $username, $email, $role, $createdAt, $useractive, $loginattempts)
    {
        $this->setUserID($userid);
        $this->setFullName($fullname);
        $this->setUsername($username);
        $this->setEmail($email);
        $this->setRole($role);
        $this->setCreatedAt($createdAt);
        $this->setUserActive($useractive);
        $this->setLoginattempts($loginattempts);
    }

    public function getUserID()
    {
        return $this->_userid;
    }

    public function getFullName()
    {
        return $this->_fullname;
    }

    public function getUsername()
    {
        return $this->_username;
    }

    public function getEmail()
    {
        return $this->_email;
    }

    public function getRole()
    {
        return $this->_role;
    }

    public function getCreatedAt()
    {
        return $this->_createdAt;
    }

    public function getUseractive()
    {
        return $this->_useractive;
    }

    public function getLoginattempts()
    {
        return $this->_loginattempts;
    }

    public function setUserID($userid)
    { // Största talet som får finnas i en SQL DATABAS
        if (($userid !== null) && (!is_numeric($userid) || $userid <= 0 || $userid > 9223372036854775807 || $this->_userid !== null)) {
            throw new UserException("User ID Error");
        }

        $this->_userid = $userid;
    }

    public function setFullName($fullname)
    {
        if (strlen($fullname) < 0 || strlen($fullname) > 255) {
            throw new UserException("User full name error");
        }

        $this->_fullname = $fullname;
    }

    public function setUsername($username)
    {
        if (strlen($username) < 0 || strlen($username) > 255) {
            throw new UserException("Username error");
        }

        $this->_username = $username;
    }

    public function setEmail($email)
    {
        if (strlen($email) < 0 || strlen($email) > 255) {
            throw new UserException("Email error");
        }

        $this->_email = $email;
    }


    public function setRole($role)
    {
        if (strlen($role) < 0 || strlen($role) > 255) {
            throw new UserException("role error");
        }

        $this->_role = $role;
    }

    public function setCreatedAt($createdAt)
    {
        if (($createdAt !== null) && date_format(date_create_from_format('d/m/Y H:i', $createdAt), 'd/m/Y H:i') != $createdAt) {
            throw new UserException("User created date time error");
        }

        $this->_createdAt = $createdAt;
    }

    public function setUseractive($useractive)
    {
        if (strtoupper($useractive) !== 'Y' && strtoupper($useractive) !== 'N') {
            throw new UserException("Useractive must have value Y or N");
        }

        $this->_useractive = $useractive;
    }

    public function setLoginattempts($loginattempts)
    {
        if (intval($loginattempts) >= 0 && intval($loginattempts) >= 3) {
            throw new UserException("loginattempts must have value 0-3");
        }

        $this->_loginattempts = $loginattempts;
    }

    public function returnUserAsArray()
    {
        $user = array();
        $user['user_id'] = $this->getUserID();
        $user['fullname'] = $this->getFullName();
        $user['username'] = $this->getUsername();
        $user['email'] = $this->getEmail();
        $user['role'] = $this->getRole();
        $user['created_at'] = $this->getCreatedAt();
        $user['useractive'] = $this->getUseractive();
        $user['loginattempts'] = $this->getLoginattempts();

        return $user;
    }

}
