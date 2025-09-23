<?php

require_once 'BaseModel.php';

class UserModel extends BaseModel {

    public function findUserById($id) {
        $sql = 'SELECT * FROM users WHERE id = '.$id;
        $user = $this->select($sql);

        return $user;
    }

    public function findUser($keyword) {
        $sql = 'SELECT * FROM users WHERE user_name LIKE %'.$keyword.'%'. ' OR user_email LIKE %'.$keyword.'%';
        $user = $this->select($sql);

        return $user;
    }

    /**
     * Authentication user
     * @param $userName
     * @param $password
     * @return array
     */
    public function auth($userName, $password) {
        $md5Password = md5($password);
        $sql = 'SELECT * FROM users WHERE name = "' . $userName . '" AND password = "'.$md5Password.'"';

        $user = $this->select($sql);
        return $user;
    }

    /**
     * Delete user by id
     * @param $id
     * @return mixed
     */
    public function deleteUserById($id) {
        $sql = 'DELETE FROM users WHERE id = '.$id;
        return $this->delete($sql);

    }

    /**
     * Update user
     * @param $input
     * @return mixed
     */
    public function updateUser($data) {
        $id = (int)$data['id'];
        $name = $data['name'] ?? '';
        $fullname = $data['fullname'] ?? '';
        $email = $data['email'] ?? '';
        $type = $data['type'] ?? 'user';
        $password = $data['password'] ?? '';

        $sql = "UPDATE users 
            SET name='$name', fullname='$fullname', email='$email', type='$type'";

        if (!empty($password)) {
            $sql .= ", password='$password'";
        }
        $sql .= " WHERE id=$id";

        return $this->update($sql);
    }


    /**
     * Insert user
     * @param $input
     * @return mixed
     */
    public function insertUser($input) {
        $name = $data['name'] ?? '';
        $fullname = $data['fullname'] ?? '';
        $email = $data['email'] ?? '';
        $type = $data['type'] ?? 'user';
        $password = $data['password'] ?? '';

        $sql = "INSERT INTO users (name, fullname, email, type, password)
                VALUES ('$name', '$fullname', '$email', '$type', '$password')";
        return $this->insert($sql);
    }

    /**
     * Search users
     * @param array $params
     * @return array
     */
    public function getUsers($params = []) {
        //Keyword
        if (!empty($params['keyword'])) {
            $sql = 'SELECT * FROM users WHERE name LIKE "%' . $params['keyword'] .'%"';

            //Keep this line to use Sql Injection
            //Don't change
            //Example keyword: abcef%";TRUNCATE banks;##
            $users = self::$_connection->multi_query($sql);

            //Get data
            $users = $this->query($sql);
        } else {
            $sql = 'SELECT * FROM users';
            $users = $this->select($sql);
        }

        return $users;
    }
}