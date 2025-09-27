<?php
require_once 'BaseModel.php';

class UserModel extends BaseModel
{
    public function findUserById($id)
    {
        $sql = "SELECT id, name, fullname, email, type 
                FROM users WHERE id = :id LIMIT 1";
        $stmt = self::$_connection->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findUser($keyword)
    {
        $sql = "SELECT id, name, fullname, email, type 
                FROM users WHERE name LIKE :kw OR email LIKE :kw";
        $stmt = self::$_connection->prepare($sql);
        $stmt->execute([':kw' => "%{$keyword}%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function auth($userName, $password)
    {
        $sql = "SELECT id, name, fullname, email, type, password 
                FROM users WHERE name = :name LIMIT 1";
        $stmt = self::$_connection->prepare($sql);
        $stmt->execute([':name' => $userName]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']); // không trả hash về
            return $user;
        }
        return null;
    }

    public function deleteUserById($id)
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = self::$_connection->prepare($sql);
        return $stmt->execute([':id' => (int)$id]);
    }

    public function updateUser($data) {
    $sql = "UPDATE users 
               SET name = :name, fullname = :fullname, email = :email, type = :type";
    $params = [
        ':name' => $data['name'],
        ':fullname' => $data['fullname'],
        ':email' => $data['email'],
        ':type' => $data['type'],
        ':id' => (int)$data['id']
    ];

    if (!empty($data['password'])) {
        $sql .= ", password = :password";
        $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    $sql .= " WHERE id = :id";
    $stmt = self::$_connection->prepare($sql);
    return $stmt->execute($params);
}


    public function insertUser($data) {
    if (empty($data['password'])) {
        throw new Exception("Password is required for new users");
    }

    $sql = "INSERT INTO users (name, fullname, email, type, password)
            VALUES (:name, :fullname, :email, :type, :password)";
    $stmt = self::$_connection->prepare($sql);
    $stmt->execute([
        ':name' => $data['name'],
        ':fullname' => $data['fullname'],
        ':email' => $data['email'],
        ':type' => $data['type'],
        ':password' => password_hash($data['password'], PASSWORD_DEFAULT)
    ]);
    return self::$_connection->lastInsertId();
}


    public function getUsers($params = [])
    {
        if (!empty($params['keyword'])) {
            $sql = "SELECT id, name, fullname, email, type 
                      FROM users WHERE name LIKE :kw OR email LIKE :kw";
            $stmt = self::$_connection->prepare($sql);
            $stmt->execute([':kw' => "%{$params['keyword']}%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $sql = "SELECT id, name, fullname, email, type FROM users";
            return self::$_connection->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
