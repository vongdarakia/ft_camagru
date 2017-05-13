<?php 
/**
 * DbItem class file
 *
 * PHP version 5.5.38
 *
 * @category  DbItem
 * @package   Camagru
 * @author    Akia Vongdara <vongdarakia@gmail.com>
 * @copyright 2017 Akia Vongdara
 * @license   Akia's Public License
 * @link      localhost:8080
 */

/**
 * A generic class to hold general data and methods.
 *
 * @category  Class
 * @package   DbItem
 * @author    Akia Vongdara <vongdarakia@gmail.com>
 * @copyright 2017 Akia Vongdara
 * @license   Akia's Public License
 * @link      localhost:8080
 */
class DbItem
{
    protected $db;
    protected $id;
    protected $table;
    
    /**
     * Constructs a user object given some values.
     *
     * @param DBConnectionObject $db    Database object we'll be using
                                        to access user data.
     * @param Array              $table The db table we're using.
     */
    function __construct($db, $table)
    {
        if (!isset($db)) {
            throw new Exception("Db must be set.", 1);
        }
        $this->db = $db;
        $this->id = 0;
        $this->table = $table;
    }

    /**
     * Sets the id of the user. Id must be greater than 0.
     *
     * @param String $value id of the user.
     *
     * @return Boolean whether set was successful or not.
     */
    public function setId($value)
    {
        if (isset($value) && $value > 0) {
            $this->id = $value;
            return true;
        }
        return false;
    }

    /**
     * Gets the id of the user.
     *
     * @return String the id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets a db object given the id. It's an object with all the
     * fields.
     *
     * @param Array $id ID of the item we're trying to get.
     *
     * @return Null or an object of the item.
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare("select * from `{$this->table}` where id=:id");
        $stmt->execute(array(":id" => $id));
        return $stmt->fetchObject();
    }

    /**
     * Removes a user from the database given the id;
     *
     * @param Int $id The id of the user to be removed.
     *
     * @return Int number of users removed. Will be 1 or 0.
     */
    public function removeById($id)
    {
        $stmt = $this->db->prepare("delete from `{$this->table}` where id=:id");
        $stmt->execute(array(":id" => $id));
        return $stmt->rowCount();
    }

    /**
     * Removes the item from the database.
     *
     * @return Int number of item removed. Will be 1 or 0.
     */
    public function remove()
    {
        return $this->removeById($this->id);
    }

    /**
     * Checks fields if they are valid. Returns 0 the moment it finds
     * an invalid field.
     *
     * @param Array $fields       Fields to be validated.
     * @param Array $class_fields Fields to validate against.
     *
     * @return Int number of valid fields.
     */
    public function validFields($fields, $class_fields)
    {
        $count = 0;
        $checkedFields = [];
        print("{$this->table} fields " . count($class_fields) ."\n");
        if (is_array($fields)) {
            foreach ($fields as $field => $val) {
                if (!in_array($field, $class_fields)) {
                    return 0;
                }
                if (!in_array($field, $checkedFields)) {
                    $checkedFields[] = $field;
                    $count += 1;
                }
            }
        }
        print("Count: {$count}\n");
        return $count;
    }
}

?>