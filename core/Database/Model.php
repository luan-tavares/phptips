<?php

namespace Core\Database;

use PDO;
use stdClass;
use Exception;
use PDOException;
use PDOStatement;
use Core\Message\Message;
use Core\Database\Connect;

abstract class Model
{

    /** @var array */
    private static $reserved = [
        "offset",
        "limit",
    ];

    /** @var array */
    protected static $safe = [
        "id",
        "created_at",
        "updated_at"
    ];

    /** @var array|null */
    protected static $required;

    /** @var object|null */
    protected $data;

    /** @var Exception|null */
    protected $fail;

    /** @var Message|null */
    protected $message;
    
    /** @var string */
    protected static $entity;

    /************************ */

    public function __construct()
    {
        $this->message = new Message;
    }

    public function __set($property, $value)
    {
        if (!$this->data) {
            $this->data = new stdClass;
        }
        $this->data->{$property} = $value;
    }

    public function __get($property)
    {
        return $this->data->{$property} ?? null;
    }

    public function __isset($property)
    {
        return isset($this->data->{$property});
    }

    /**
     * Get $data
     *
     * @return Object|null
     */
    public function data(): ?Object
    {
        return $this->data;
    }

    /**
     * Get $fail
     *
     * @return Exception|null
     */
    public function fail(): ?Exception
    {
        return $this->fail;
    }

    /**
     * get $message
     *
     * @return Message|null
     */
    public function message(): ?Message
    {
        return $this->message;
    }

    /**
     * build all queries for Read in CRUD
     *
     * @param string $select
     * @param string|null $params
     * @access protected
     * @return PDOStatement|null
     */
    protected function read(string $select, string $params = null): ?PDOStatement
    {
        try {
            $statement = Connect::instance()->prepare($select);
            if ($params) {
                parse_str($params, $params);
                foreach ($params as $key => $value) {
                    if (in_array($key, self::$reserved)) {
                        $statement->bindValue(":{$key}", $value, PDO::PARAM_INT);
                        continue;
                    }
                    $statement->bindValue(":{$key}", $value, PDO::PARAM_STR);
                }
                $statement->execute();
                return $statement;
            }
        } catch (PDOException $err) {
            $this->fail = $err;
            return null;
        }
    }

    /**
     * @param $terms
     * @param $params
     * @param $columns
     * @return
     */
    protected function findOne(string $terms, string $params, string $columns): ?Model
    {
        $find = $this->read("SELECT {$columns} FROM ". static::$entity ." WHERE {$terms} LIMIT 1", $params);
        if ($this->fail || !$find->rowCount()) {
            $this->message->info("Cadastro não encontrado");
            return null;
        }
        return $find->fetchObject(static::class);
    }

    /**
     * build all queries for Create in CRUD
     *
     * @param array $data
     * @access protected
     * @return int|null
     */
    protected function create(array $data): ?int
    {
        $columns = implode(', ', array_keys($data));
        $values = ":". implode(', :', array_keys($data));
        try {
            $query = "INSERT INTO ". static::$entity ." ({$columns}) VALUES ({$values})";
            $statement = Connect::instance()->prepare($query);
            $statement->execute($data);
            return Connect::instance()->lastInsertId();
        } catch (PDOException $err) {
            $this->fail = $err;
            return null;
        }
    }

    /**
     * build all queries for Update in CRUD
     *
     * @param array $data
     * @param string $terms - terms in WHERE clause
     * @param string $params - values for terms in WHERE clause
     * @access protected
     * @return int|null
     */
    protected function update(array $data, string $terms, string $params): ?int
    {
        $columns = implode(", ", array_map(function ($v) {
            return "{$v} = :{$v}";
        }, array_keys($data)));
        parse_str($params, $params);
        try {
            $query = "UPDATE ". static::$entity ." SET {$columns} WHERE {$terms}";
            $statement = Connect::instance()->prepare($query);
            $statement->execute(array_merge($data, $params));
            return $statement->rowCount();
        } catch (PDOException $err) {
            $this->fail = $err;
            return null;
        }
    }

    /**
     * build all queries for Delete in CRUD
     *
    * @param string $terms
    * @param string $params
    * @return int|null
    */
    protected function delete(string $terms, string $params): ?int
    {
        try {
            $statement = Connect::instance()->prepare("DELETE FROM ". static::$entity ." WHERE {$terms}");
            parse_str($params, $params);
            $statement->execute($params);
            return $statement->rowCount();
        } catch (PDOException $exception) {
            $this->fail = $exception;
            return null;
        }
    }

    /**
     * Remove only read columns from Data
     *
     * @return array|null
     */
    protected function safe(): ?array
    {
        if (!$this->data) {
            return null;
        }
        $safe = (array) $this->data;
        foreach (static::$safe as $unsetValue) {
            unset($safe[$unsetValue]);
        }
        return $this->filter($safe);
    }

    /**
     * Sanitize injection and tags
     *
     * @param array $data
     * @return array
     */
    protected function filter(array $data): array
    {
        $filter = [];
        foreach ($data as $key => $value) {
            $filter[$key] = ((is_null($value))?(null):(filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS)));
        }
        return $filter;
    }

    /**
     * Apply all the rules include required
     *
     * @return bool
     */
    protected function rules(): bool
    {
        $message =[];
        foreach (static::$rules as $key => $value) {
            $rules = explode(":", $value);
            foreach ($rules as $rule) {
                switch ($rule) {
                    case "required":
                        if (empty($this->{$key})) {
                            $message[] = "Campo \"{$key}\" não deve ser nulo.";
                        }
                    break;
                    case "email":
                        if (!empty($this->{$key}) && !filter_var($this->{$key}, FILTER_VALIDATE_EMAIL)) {
                            $message[] = "Campo \"{$key}\" deve ser um email.";
                        }
                    break;
                    case "url":
                        if (!empty($this->{$key}) && !filter_var($this->{$key}, FILTER_VALIDATE_URL)) {
                            $message[] = "Campo \"{$key}\" deve ser uma url.";
                        }
                    break;
                    case "password":
                        if (!is_passwd($this->{$key})) {
                            $message[] = "Campo \"{$key}\" deve ser uma hash.";
                        }
                    break;
                }
            }
        }
        if ($message) {
            $this->message->error($message);
            $this->fail = new Exception("Models Rules Boundary avoid the CRUD action");
            return false;
        }
        return true;
    }
}
