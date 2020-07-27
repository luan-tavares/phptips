<?php

namespace App\Models;

use Core\Database\Model;
use PDO;

class User extends Model
{
    /**
     * @override
     */
    protected static $entity = "users";

    protected static $rules = [
        "firstname" => "required",
        "lastname" => "required",
        "email" => "required:email",
        "password" => "required:hash",
        "domain" => "url"
    ];
  
    /**
     * @return User|Null
     */
    public function bootstrap(
        string $firstname = null,
        string $lastname = null,
        string $email = null,
        string $document = null,
        string $password = null
    ): ?User {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->document = $document;
        $this->password = $password;
        $this->safe();
        return $this;
    }

    /**
     * @param $id
     * @return
     */
    public function findById($id, string $columns = "*"): ?User
    {
        return $this->findOne("id = :id", "id={$id}", $columns);
    }

    /**
     * @param $email
     * @return
     */
    public function findByEmail(string $email, string $columns = "*"): ?User
    {
        return $this->findOne("email = :email", "email={$email}", $columns);
    }

    public function all(int $limit = 30, int $offset = 0, $columns = "*") :?array
    {
        $all = $this->read(
            "SELECT {$columns} FROM ". self::$entity ." LIMIT :limit OFFSET :offset",
            "limit={$limit}&offset={$offset}"
        );
        if ($this->fail || !$all->rowCount()) {
            return [];
        }
        return $all->fetchAll(PDO::FETCH_CLASS, __CLASS__);
    }

    public function save(): ?Model
    {
        if (!$this->rules()) {
            return null;
        }

        $idUserByEmail = null;
        if ($userByEmail = $this->findByEmail($this->email)) {
            $idUserByEmail = $userByEmail->id;
        }

        if ($this->id) {
            $idUser = $this->id;

            if ($idUserByEmail !== $idUser) {
                $this->message->warning("Outro usuário já possui este email");
                return null;
            }
            $this->update($this->safe(), "id=:id", "id={$idUser}");
            if ($this->fail) {
                $this->message->error("Falha na atualização! {$this->fail->getMessage()}");
                return null;
            }
            $this->message->success("Atualização efetuado com sucesso");
            return $this;
        }

        if ($idUserByEmail) {
            $this->message->warning("Email já existente na base para cadastrar");
            return null;
        }

        $idUser = $this->create($this->safe());
        if ($this->fail) {
            $this->message->error("Falha no cadastro! {$this->fail->getMessage()}");
            return null;
        }

        $this->data = $this->findById($idUser)->data();
       
        $this->message->success("Cadastro efetuado com sucesso");
        return $this;
    }

    /**
    * @return null|Model
    */
    public function destroy(): ?Model
    {
        if (empty($this->id)) {
            $this->message->warning("Insira um Id para excluir um registro em \"". __CLASS__ ."\"");
            return $this;
        }
        $rowsDeleted = $this->delete("id = :id", "id={$this->id}");
        
        if ($this->fail()) {
            $this->message->error("Não foi possível remover o usuário");
            return null;
        }
      
        $this->message->success("Usuário removido com sucesso");
        if (!$rowsDeleted) {
            $this->message->warning("Este id de usuário não existe em \"". __CLASS__ ."\"");
        }
        $this->data = null;
        return $this;
    }
}
