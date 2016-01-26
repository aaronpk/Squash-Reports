<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Initialize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('
        CREATE TABLE `emoji` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `org_id` int(11) NOT NULL DEFAULT \'0\',
          `shortname` varchar(255) DEFAULT NULL,
          `filename` varchar(255) DEFAULT NULL,
          `url` varchar(512) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');

        DB::statement('
        CREATE TABLE `entries` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `org_id` int(11) NOT NULL,
          `user_id` int(11) NOT NULL,
          `group_id` int(11) DEFAULT NULL,
          `created_at` datetime NOT NULL,
          `command` varchar(100) DEFAULT NULL,
          `text` text,
          `slack_userid` varchar(255) DEFAULT NULL,
          `slack_username` varchar(255) DEFAULT NULL,
          `slack_channelid` varchar(255) DEFAULT NULL,
          `slack_channelname` varchar(255) DEFAULT NULL,
          `num_likes` int(11) NOT NULL DEFAULT \'0\',
          `num_comments` int(11) NOT NULL DEFAULT \'0\',
          PRIMARY KEY (`id`),
          KEY `group_date` (`group_id`,`created_at`),
          KEY `user_date` (`user_id`,`created_at`),
          KEY `text` (`text`(191))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');

        DB::statement('
        CREATE TABLE `groups` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `org_id` int(11) NOT NULL,
          `shortname` varchar(255) NOT NULL DEFAULT \'\',
          `photo_url` varchar(255) DEFAULT NULL,
          `cover_photo` varchar(255) DEFAULT NULL,
          `timezone` varchar(255) DEFAULT NULL,
          `created_at` datetime NOT NULL,
          `created_by` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');

        DB::statement('
        CREATE TABLE `orgs` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(255) DEFAULT NULL,
          `shortname` varchar(255) DEFAULT NULL,
          `created_at` datetime DEFAULT NULL,
          `created_by` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');

        DB::statement('
        CREATE TABLE `responses` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `entry_id` int(11) NOT NULL,
          `created_at` datetime DEFAULT NULL,
          `user_id` int(11) NOT NULL,
          `comment` text,
          `like` tinyint(4) DEFAULT NULL,
          `reacji` varchar(255) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `entry_id` (`entry_id`),
          KEY `entry_user` (`entry_id`,`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        DB::statement('
        CREATE TABLE `slack_channels` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `slack_team_id` int(11) NOT NULL,
          `slack_channelid` varchar(255) NOT NULL DEFAULT \'\',
          `slack_channelname` varchar(255) DEFAULT NULL,
          `org_id` int(11) NOT NULL,
          `group_id` int(11) NOT NULL,
          `created_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4
        ');

        DB::statement('
        CREATE TABLE `slack_teams` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `slack_teamid` varchar(255) NOT NULL DEFAULT \'\',
          `slack_teamname` varchar(255) DEFAULT NULL,
          `slack_url` varchar(255) DEFAULT NULL,
          `org_id` int(11) NOT NULL,
          `slack_token` varchar(255) DEFAULT NULL,
          `created_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4
        ');

        DB::statement('
        CREATE TABLE `slack_users` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `org_id` int(11) DEFAULT NULL,
          `slack_userid` varchar(255) NOT NULL DEFAULT \'\',
          `slack_username` varchar(255) DEFAULT NULL,
          `user_id` int(11) NOT NULL,
          `created_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4
        ');

        DB::statement('
        CREATE TABLE `subscriptions` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `group_id` int(11) DEFAULT NULL,
          `search_term` varchar(255) DEFAULT NULL,
          `frequency` enum(\'daily\',\'weekly\') DEFAULT NULL,
          `daily_localtime` int(11) DEFAULT NULL,
          `weekly_dow` char(1) DEFAULT NULL,
          `created_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `subscription` (`user_id`,`group_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4
        ');

        DB::statement('
        CREATE TABLE `users` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `org_id` int(11) DEFAULT NULL,
          `username` varchar(255) DEFAULT NULL,
          `email` varchar(255) DEFAULT NULL,
          `display_name` varchar(255) DEFAULT NULL,
          `photo_url` varchar(255) DEFAULT NULL,
          `cover_photo` varchar(255) DEFAULT NULL,
          `location` varchar(255) DEFAULT NULL,
          `timezone` varchar(255) DEFAULT NULL,
          `created_at` datetime DEFAULT NULL,
          `remember_token` varchar(200) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4
        ');
    }

    public function down() {

    }
}
