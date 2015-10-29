<?php

if ($updater_utils->check_version("6.1.0")) {

    // Add oauth2 tables.
    if (!$updater_utils->has_updated('rogo1559_oauth')) {
        $createsql = "CREATE TABLE oauth_clients (
            client_id VARCHAR(80) NOT NULL,
            client_secret VARCHAR(80),
            redirect_uri VARCHAR(2000) NOT NULL,
            grant_types VARCHAR(80),
            scope VARCHAR(100),
            user_id VARCHAR(80),
            CONSTRAINT clients_client_id_pk PRIMARY KEY (client_id),
            KEY `idx_user_id` (`user_id`))";
        $updater_utils->execute_query($createsql, true);
        $createsql = "CREATE TABLE oauth_access_tokens (
            access_token VARCHAR(40) NOT NULL,
            client_id VARCHAR(80) NOT NULL,
            user_id VARCHAR(255),
            expires TIMESTAMP NOT NULL,
            scope VARCHAR(2000),
            CONSTRAINT access_token_pk PRIMARY KEY (access_token))";
        $updater_utils->execute_query($createsql, true);
        $createsql = "CREATE TABLE oauth_authorization_codes (
            authorization_code VARCHAR(40) NOT NULL,
            client_id VARCHAR(80) NOT NULL,
            user_id VARCHAR(255),
            redirect_uri VARCHAR(2000),
            expires TIMESTAMP NOT NULL,
            scope VARCHAR(2000),
            CONSTRAINT auth_code_pk PRIMARY KEY (authorization_code))";
        $updater_utils->execute_query($createsql, true);
        $createsql = "CREATE TABLE oauth_refresh_tokens (
            refresh_token VARCHAR(40) NOT NULL,
            client_id VARCHAR(80) NOT NULL,
            user_id VARCHAR(255), expires TIMESTAMP NOT NULL,
            scope VARCHAR(2000),
            CONSTRAINT refresh_token_pk PRIMARY KEY (refresh_token),
            KEY `idx_user_id` (`user_id`))";
        $updater_utils->execute_query($createsql, true);
        $createsql = "CREATE TABLE oauth_users (
            username VARCHAR(255) NOT NULL,
            password VARCHAR(2000),
            first_name VARCHAR(255),
            last_name VARCHAR(255),
            CONSTRAINT username_pk PRIMARY KEY (username))";
        $updater_utils->execute_query($createsql, true);
        $createsql = "CREATE TABLE oauth_scopes (
            scope TEXT,
            is_default BOOLEAN)";
        $updater_utils->execute_query($createsql, true);
        $createsql = "CREATE TABLE oauth_jwt (
            client_id VARCHAR(80) NOT NULL,
            subject VARCHAR(80),
            public_key VARCHAR(2000),
            CONSTRAINT jwt_client_id_pk PRIMARY KEY (client_id))";
        $updater_utils->execute_query($createsql, true);
        
        // Add oauth settings config file.
        $new_lines = array("\$cfg_oauth_access_lifetime = 1209600; // length of access token lifetime.\n", "\$cfg_oauth_refresh_token_lifetime = 1209600; // length of refresh token lifetime.\n" , "\$cfg_oauth_always_issue_new_refresh_token = true; // enable or disable refresh tokens.\n");
        $target_line = '$debug_lang_string';
        $updater_utils->add_line($string, '$cfg_oauth_access_lifetime', $new_lines, 28, $cfg_web_root, $target_line, +2);

        $updater_utils->record_update('rogo1559_oauth');
    }
}