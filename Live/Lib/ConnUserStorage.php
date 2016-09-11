<?php

namespace Live\Lib;


class ConnUserStorage
{
    static function getInstance()
    {
        static $table;

        if (!$table) {
            $table = new \swoole_table(2 ^ 32);

            $table->column('fd', \swoole_table::TYPE_INT, 4);
            $table->column('room_id', \swoole_table::TYPE_INT, 8);
            $table->column('uid', \swoole_table::TYPE_INT, 8);
            $table->column('nickname', \swoole_table::TYPE_STRING, 12);
            $table->column('avatar', \swoole_table::TYPE_STRING, 70);
            $table->column('lv', \swoole_table::TYPE_INT, 4);
            $table->column('is_vip', \swoole_table::TYPE_INT, 1);
            $table->column('is_tycoon', \swoole_table::TYPE_INT, 1);

            $table->create();
        }

        return $table;
    }

//    static function msg()
//    {
//        static $table;
//
//        if (!$table) {
//            $table = new \swoole_table(2 ^ 32);
//
//            $table->column('uid', \swoole_table::TYPE_INT, 8);
//            $table->column('room_id', \swoole_table::TYPE_INT, 8);
//            $table->column('msg', \swoole_table::TYPE_STRING, 500);
//            $table->column('ts', \swoole_table::TYPE_INT, 8);
//
//            $table->create();
//        }
//
//        return $table;
//    }
}