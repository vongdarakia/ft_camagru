<?php 
/**
 * User class file
 *
 * Resources:
 *      Common Passwords
 *      https://github.com/danielmiessler/SecLists/blob/master/Passwords/10_million_password_list_top_500.txt
 *
 * PHP version 5.5.38
 *
 * @category  User
 * @package   Camagru
 * @author    Akia Vongdara <vongdarakia@gmail.com>
 * @copyright 2017 Akia Vongdara
 * @license   No License
 * @link      localhost:8080
 */

require_once 'DbItem.php';

/**
 * User class that holds all its database operations.
 *
 * @category  Class
 * @package   User
 * @author    Akia Vongdara <vongdarakia@gmail.com>
 * @copyright 2017 Akia Vongdara
 * @license   No License
 * @link      localhost:8080
 */
class User extends DbItem
{
    private $_first;
    private $_last;
    private $_username;
    private $_email;
    private $_password;
    private $_verified;
    public static $VERIFIED_FAILED = 0;
    public static $VERIFIED_SUCCESS = 1;
    public static $ALREADY_VERIFIED = 2;
    private static $_fields = array(
        "id", "first", "last", "username", "email", "password", "verified"
    );
    
    /**
     * Constructs a user object given some values.
     *
     * @param DBConnectionObject $db     Database object we'll be using
                                         to access user data.
     * @param Array              $fields Fields we're setting for the object.
     */
    function __construct($db, $fields=null)
    {
        parent::__construct($db, 'user');
        $this->_first = "";
        $this->_last = "";
        $this->_username = "";
        $this->_email = "";
        $this->_password = "";
        $this->_verified = 0;
        if (isset($fields)) {
            if ($this->validFields($fields, User::$_fields)) {
                $this->setFields($fields);
            } else {
                throw new Exception("Invalid fields.", 1);
            }
        }
    }

    /**
     * Gets the first name.
     *
     * @return String the first name.
     */
    public function getFirstName()
    {
        return $this->_first;
    }

    /**
     * Sets the first name.
     *
     * @param String $value First name.
     *
     * @return Boolean whether set was successful or not.
     */
    public function setFirstName($value)
    {
        if (DbItem::validNonEmptyString($value) && ctype_alnum($value)) {
            $this->_first = $value;
            return true;
        }
        return false;
    }

    /**
     * Gets the last name.
     *
     * @return String the last name.
     */
    public function getLastName()
    {
        return $this->_last;
    }

    /**
     * Sets the last name.
     *
     * @param String $value Last name.
     *
     * @return Boolean whether set was successful or not.
     */
    public function setLastName($value)
    {
        if (DbItem::validNonEmptyString($value) && ctype_alnum($value)) {
            $this->_last = $value;
            return true;
        }
        return false;
    }

    /**
     * Gets the username.
     *
     * @return String the username.
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * Sets the username.
     *
     * @param String $value username.
     *
     * @return Boolean whether set was successful or not.
     */
    public function setUsername($value)
    {
        if (DbItem::validNonEmptyString($value) && ctype_alnum($value)) {
            $this->_username = $value;
            return true;
        }
        return false;
    }

    /**
     * Gets the email.
     *
     * @return String the email.
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * Sets the email.
     *
     * @param String $value email.
     *
     * @return Boolean whether set was successful or not.
     */
    public function setEmail($value)
    {
        if (DbItem::validNonEmptyString($value) && $this->validEmail($value)) {
            $this->_email = $value;
            return true;
        }
        return false;
    }

    /**
     * Gets the password.
     *
     * @return String the password.
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Checks if password is one of the top 500 common passwords.
     *
     * @param String $pass password.
     *
     * @return Boolean whether set was successful or not.
     */
    public function isPassCommon($pass)
    {
        $handle = fopen(CONFIG_PATH . "/500_common_passwords.txt", "r");
        $is_common = false;

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                // process the line read.
                $line = trim($line);
                if ($line == $pass) {
                    $is_common = true;
                    break;
                }
            }
            fclose($handle);
        } else {
            // error opening the file.
            throw new Exception("Couldn't open file.", 1);
        } 
        return $is_common;
    }

    /**
     * Sets the password.
     *
     * @param String $value password.
     *
     * @return Boolean whether set was successful or not.
     */
    public function setPassword($value)
    {
        if (DbItem::validNonEmptyString($value) && strlen($value) >= 6) {
            $len = strlen($value);
            $has_num = false;
            $has_alph = false;

            for ($i=0; $i < $len; $i++) { 
                if (is_numeric($value[$i])) {
                    $has_num = true;
                }
                if (ctype_alpha($value[$i])) {
                    $has_alph = true;
                }
            }

            if ($has_num && $has_alph) {
                if (!$this->isPassCommon($value)) {
                    $this->_password = hash('whirlpool', $value);
                    return true;
                } else {
                    throw new Exception("This password is too common.", 1);
                }
            } else {
                throw new Exception(
                    "password must have at least 6 " .
                    "characters and at least 1 number.",
                    1
                );
            }
        }
        return false;
    }

    /**
     * Gets the verification status.
     *
     * @return Int the verification status.
     */
    public function getVerified()
    {
        return $this->_verified;
    }

    /**
     * Sets the verification status.
     *
     * @param Int $value verification status.
     *
     * @return Boolean whether set was successful or not.
     */
    public function setVerified($value)
    {
        if (is_numeric($value)
            && ($value == 1 || $value == 0)
        ) {
            $this->_verified = $value;
            return true;
        }
        return false;
    }
    
    /**
     * Returns whether the email is valid.
     * Resource:
     *      http://stackoverflow.com/questions/12026842/how-to-validate-an-email-address-in-php
     *
     * @param String $email Email we're validating.
     *
     * @return Boolean whether email is valid or not.
     */
    public function validEmail($email)
    {
        $pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|'.
        '(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C'.
        '[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)'.
        '(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F'.
        '\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F'.
        '\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))'.
        '(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D'.
        '\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-'.
        '\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*'.
        '\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+'.
        '(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:'.
        '(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:'.
        '(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9]'.
        '[:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:'.
        '[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:'.
        '[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:)'.
        '{5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}'.
        '(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|'.
        '(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|'.
        '(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

        return (preg_match($pattern, $email) === 1);
    }

    /**
     * Updates the object data in the database.
     *
     * @return Int number of users saved. Will be 1 or 0.
     */
    public function save()
    {
        $qry = "update `user`
            set
                first=:first,
                last=:last,
                username=:username,
                email=:email,
                password=:password,
                verified=:verified
            where id=:id";
        $stmt = $this->db->prepare($qry);
        $stmt->execute(
            array(
                ":first" => $this->_first,
                ":last" => $this->_last,
                ":username" => $this->_username,
                ":email" => $this->_email,
                ":password" => $this->_password,
                ":verified" => $this->_verified,
                ":id" => $this->id
            )
        );
        return $stmt->rowCount();
    }

    /**
     * Verifies a user's account.
     *
     * @param String $code Email confirmation code.
     *
     * @return Boolean Success
     */
    public function verify($code) {
        $qry = "update `user` u
            inner join `email_confirmation` ec on u.id = ec.author_id
            set verified=1
            where ec.code=:code";
        $stmt = $this->db->prepare($qry);
        $stmt->execute(array(":code" => $code));

        if ($stmt->rowCount() == 0) {
            $qry = "select first from `user` u
            inner join `email_confirmation` ec on ec.author_id = u.id
            where ec.`code`=:code and verified=1";
            $stmt = $this->db->prepare($qry);
            $stmt->execute(array(":code" => $code));
            $user = $stmt->fetchObject();

            if ($user) {
                return User::$ALREADY_VERIFIED;
            }
            return User::$VERIFIED_FAILED;
        }
        return User::$VERIFIED_SUCCESS;
    }

    /**
     * Gets a db user object given the email. This is not the same instance of
     * this User class. The object however will have all its fields accessible
     * to the programmer.
     *
     * @param String $email email of the user we're trying to get.
     *
     * @return Null or an object of the user.
     */
    public function getUserByEmail($email)
    {
        $stmt = $this->db->prepare("select * from `user` where email=:email");
        $stmt->execute(array(':email' => $email));
        return $stmt->fetchObject();
    }

    /**
     * Gets a db user object given the username. This is not the same instance of
     * this User class. The object however will have all its fields accessible
     * to the programmer.
     *
     * @param String $username username of the user we're trying to get.
     *
     * @return Null or an object of the user.
     */
    public function getUserByUsername($username)
    {
        $stmt = $this->db->prepare("select * from `user` where username=:username");
        $stmt->execute(array(':username' => $username));
        return $stmt->fetchObject();
    }

    /**
     * Sets the instance with the database object retrieved from
     * PDO.
     *
     * @param Int $obj DB object with all the field values.
     *
     * @return Boolean whether or not there is a db obj.
     */
    private function _setWithObj($obj)
    {
        if ($obj) {
            $this->id = $obj->id;
            $this->_first = $obj->first;
            $this->_last = $obj->last;
            $this->_username = $obj->username;
            $this->_email = $obj->email;
            $this->_password = $obj->password;
            $this->_verified = $obj->verified;
            unset($obj);
            return true;
        } else {
            $this->id = 0;
            $this->_first = "";
            $this->_last = "";
            $this->_username = "";
            $this->_email = "";
            $this->_password = "";
            $this->_verified = "";
        }
        return false;
    }

    /**
     * Loads the user data to this object given the ID.
     *
     * @param Int $id ID of the user we're trying to get.
     *
     * @return Boolean on whether it was successful or not.
     */
    public function loadById($id)
    {
        $result = $this->getById($id);
        return $this->_setWithObj($result);
    }

    /**
     * Loads the user data to this object given the email.
     *
     * @param String $email Email of the user we're trying to get.
     *
     * @return Boolean on whether it was successful or not.
     */
    public function loadByEmail($email)
    {
        $result = $this->getUserByEmail($email);
        return $this->_setWithObj($result);
    }

    /**
     * Loads the user data to this object given the username.
     *
     * @param String $username Username of the user we're trying to get.
     *
     * @return Boolean on whether it was successful or not.
     */
    public function loadByUsername($username)
    {
        $result = $this->getUserByUsername($username);
        return $this->_setWithObj($result);
    }

    /**
     * Sets the fields of the object if given.
     *
     * @param Array $fields Fields we're going to set the object with.
     *
     * @return Boolean on whether setting was successful or not.
     */
    public function setFields($fields)
    {
        $res = false;

        if (isset($fields) && is_array($fields)) {
            if (array_key_exists('id', $fields)) {
                if (!$this->setId($fields['id'])) {
                    throw new Exception("id must be greater than 0.", 1);
                }
            }
            if (array_key_exists('first', $fields)) {
                if (!$this->setFirstName($fields['first'])) {
                    throw new Exception("first name must be alphanumeric.", 1);
                }
            }
            if (array_key_exists('last', $fields)) {
                if (!$this->setLastName($fields['last'])) {
                    throw new Exception("last name must be alphanumeric.", 1);
                }
            }
            if (array_key_exists('username', $fields)) {
                if (!$this->setUsername($fields['username'])) {
                    throw new Exception("username must be alphanumeric.", 1);
                }
            }
            if (array_key_exists('email', $fields)) {
                if (!$this->setEmail($fields['email'])) {
                    throw new Exception("email is invalid.", 1);
                }
            }
            if (array_key_exists('password', $fields)) {
                if (!$this->setPassword($fields['password'])) {
                    return false;
                }
            }
            if (array_key_exists('verified', $fields)) {
                if (!$this->setVerified($fields['verified'])) {
                    throw new Exception("verified is invalid.", 1);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Adds a user to the database given a list of fields. Must have all 4 values.
     *
     * @param Array $fields Values of the users we're adding.
     *
     * @return Boolean if add was successful.
     */
    public function add($fields)
    {
        if ($this->validFields($fields, User::$_fields) == count(User::$_fields) - 1
            && $this->setFields($fields)
        ) {
            $stmt = $this->db->prepare(
                "insert into `{$this->table}`
                (first, last, username, email, password, verified)
                values (:first, :last, :username, :email, :password, :verified)"
            );
            $stmt->execute(
                array(
                    ":first" => $this->_first,
                    ":last" => $this->_last,
                    ":username" => $this->_username,
                    ":email" => $this->_email,
                    ":password" => $this->_password,
                    ":verified" => $this->_verified
                )
            );
            return $stmt->rowCount() == 1;
        }
        return 0;
    }

    /**
     * Gets whether or not the email and the password matches.
     *
     * @param String $email    Email we're trying to find.
     * @param String $password Password to check if it matches the given email.
     *
     * @return Boolean on if the password and email matches.
     */
    public function passwordMatchesEmail($email, $password)
    {
        $hashedPass = hash('whirlpool', $password);
        $qry = "select * from `user` where email=:email and password=:password";
        $stmt = $this->db->prepare($qry);
        $stmt->execute(array(':email' => $email, ':password' => $hashedPass));
        return $stmt->rowCount() == 1;
    }

    /**
     * Gets whether or not the username and the password matches.
     *
     * @param String $username Username we're trying to find.
     * @param String $password Password to check if it matches the given username.
     *
     * @return Boolean on if the password and username matches.
     */
    public function passwordMatchesUsername($username, $password)
    {
        $hashedPass = hash('whirlpool', $password);
        $qry = "select * from `user` where username=:username and password=:pass";
        $stmt = $this->db->prepare($qry);
        $stmt->execute(array(':username' => $username, ':pass' => $hashedPass));
        return $stmt->rowCount() == 1;
    }
}

?>