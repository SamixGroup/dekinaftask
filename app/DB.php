<?php


namespace app;


use mysqli;

class DB
{
    protected static string $db = 'admin_kakashi';
    protected static string $table = 'task_users';
    protected static string $pass = '';
    protected static string $user = '';
    protected static mysqli $connection;

    public static function setCreditionals($user, $pass)
    {
        self::$pass = $pass;
        self::$user = $user;
    }

    public static function connect()
    {
        self::$connection = new mysqli('localhost', self::$user, self::$pass, self::$db);
    }

    public static function save_moder($name, $id)
    {
        $name = self::$connection->real_escape_string($name);
        if ((new User($id))->get_role()) return self::$connection->query("update `" . self::$table . "` set `role`=2 `name`={$name} where `user_id`=$id");
        return self::$connection->query("insert into `" . self::$table . "` (`user_id`,`name`,`role`) values ($id,'$name',2)");
    }

    public static function get_user_role($user_id)
    {
        return self::$connection->query("select `role` from `" . self::$table . "` where `user_id` = $user_id")->fetch_assoc()['role'];
    }

    public static function get_user_name($user_id)
    {
        $result = self::$connection->query("select `full_name` from `task_cv` where `user_id` = $user_id");
        return ($result->num_rows == 0) ? false : $result->fetch_assoc()['full_name'];
    }

    public static function get_user_phone($user_id)
    {
        $result = self::$connection->query("select `phone` from `task_cv` where `user_id` = $user_id");
        return ($result->num_rows == 0) ? false : $result->fetch_assoc()['phone'];
    }

    public static function get_user_birth_date($user_id)
    {
        $result = self::$connection->query("select `date_of_birth` from `task_cv` where `user_id` = $user_id");
        return ($result->num_rows == 0) ? false : $result->fetch_assoc()['date_of_birth'];
    }

    public static function get_user($user_id)
    {
        return self::$connection->query("select * from `" . self::$table . "` where `user_id` = $user_id")->fetch_assoc();
    }

    public static function get_user_info($user_id)
    {
        return self::$connection->query("select * from `task_cv` where `user_id` = $user_id")->fetch_assoc();
    }

    public static function add_user($user_id)
    {
        return self::$connection->query("insert into " . self::$table . "(user_id,role) values($user_id,3)");
    }

    static function set_user_fullname($user_id, $name)
    {
        $result = self::$connection->query("select * from `task_cv` where `user_id` = $user_id");
        $sql = ($result->num_rows != 0) ? "update task_cv set full_name = '$name' where user_id = $user_id" : "insert into task_cv(user_id,full_name) values ($user_id, '$name')";
        return self::$connection->query($sql);
    }

    static function set_user_birth_date($user_id, $date)
    {
        $sql = "update task_cv set date_of_birth = '$date' where user_id = $user_id";
        return self::$connection->query($sql);
    }

    static function set_user_phone($user_id, $phone)
    {
        $sql = "update task_cv set phone = '$phone' where user_id = $user_id";
        return self::$connection->query($sql);
    }

    static function update_user($user_id, $column, $value)
    {
        $sql = "update task_cv set " . $column . " = '$value' where user_id = $user_id";
        return self::$connection->query($sql);
    }

    public static function get($user_id, $column)
    {
        $sql = "select " . $column . " from task_cv where user_id = $user_id";
        return self::$connection->query($sql)->fetch_assoc()[$column];
    }


    static function get_moders()
    {
        $sql = "select * from " . self::$table . " where role=2";
        return self::$connection->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    static function delete_moder($moder_id)
    {
        $sql = "delete from " . self::$table . " where id=$moder_id";
        return self::$connection->query($sql);
    }


    static function add_review($user_id, $moder_id)
    {
        return self::$connection->query("insert into task_reviews(user_id,moder_id) values ($user_id,$moder_id)");
    }

    static function get_reviews($moder_id, $marked = 0)
    {
        $result = self::$connection->query("select * from task_reviews where moder_id={$moder_id} and marked=$marked");
        return ($result->num_rows == 0) ? null : $result->fetch_all(1);
    }

    static function get_review($review_id)
    {
        $result = self::$connection->query("select task_cv.full_name,task_cv.path,task_cv.user_id,task_reviews.mark from task_reviews inner join task_cv on task_cv.user_id = task_reviews.user_id where task_reviews.id={$review_id}");
        return ($result->num_rows == 0) ? null : $result->fetch_assoc();
    }

    static function mark_review($id, $mark)
    {
        return self::$connection->query("update task_reviews set mark=$mark , marked=1 where id=$id");
    }
}
