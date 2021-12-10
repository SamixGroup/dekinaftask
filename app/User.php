<?php


namespace app;


class User extends \lib\User
{

    public function get_role()
    {
        return DB::get_user_role($this->id) ?? 0;
    }

    public function save_or_ignore()
    {
        if (empty(DB::get_user($this->id))) {
            return DB::add_user($this->id);
        }
        return 1;
    }

    public function get_name()
    {
        return DB::get_user_name($this->id);
    }

    public function get_birth_date()
    {
        return DB::get_user_birth_date($this->id);
    }

    public function get_phone()
    {
        return DB::get_user_phone($this->id);
    }


    public function set_name($name)
    {
        return DB::set_user_fullname($this->id, $name);
    }

    public function set_phone($phone)
    {
        return DB::set_user_phone($this->id, $phone);
    }

    public function set_date($date)
    {
        DB::set_user_birth_date($this->id, $date);
    }

    public function set($column, $value)
    {
        DB::update_user($this->id, $column, $value);
    }

    public function get($column = null)
    {
        if (!$column) return DB::get_user($this->id);
        return DB::get($this->id, $column);
    }

    function get_info(): ?array
    {
        return DB::get_user_info($this->id);
    }

}


