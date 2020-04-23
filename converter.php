<?php
    
    /*
     * This script is for converting joomla database to wordpress database.
     * note: this is beta and won't work for your purpose i think!
     * @author: alireza bazargani
     *
     * let's go so ...
     */


     /*
     * Include wordpress libs to make slugs from title automaticaly.
     */
    define( 'ABSPATH', dirname(dirname(__FILE__)) . '/' );
    define( 'WPINC', 'wp-includes' );
    require 'wp-functions.php';

    /*
     * Load application environments.
     */
    $env = require 'env.php';

    /**
     * @param $env
     * @return false|mysqli
     * to make a MYSQLI Procedural connection.
     */
    function make_handle($env, $side = 'joomla') {
        $servername = $env['db_host'];
        $username = $env['db_username'];
        $password = $env['db_password'];
        $db_name = ($side == 'joomla') ? $env['joomla_db_name'] : $env['wordpress_db_name'];

        // Create connection
        $conn = mysqli_connect($servername, $username, $password, $db_name);

        // Check connection
        if (!$conn) {
            echo "<pre>function: make_handle($side) -> <span style='color: orangered; font-weight: bold;'>";
            echo "Connection failed: " . mysqli_connect_error();
            echo "</span></pre>";
            die();

        }


        echo "<pre>function: make_handle($side) -> <span style='color: #00cc00; font-weight: bold;'>Connected successfully.</span></pre>";
        return $conn;
    }


    function move_articles($env) {
        $jconn = make_handle($env);
        $wconn = make_handle($env, 'wordpress');

        $jsql = "
                SELECT 
                `id`,
                `title`,
                `alias` AS 'joomla_slug',
                `introtext` AS 'lead',
                `metadesc` AS 'meta_description',
                `metakey` AS 'meta_keywords',
                `fulltext` AS 'content',
                `catid` AS 'category',
                `created_by` AS 'author',
                `created` AS 'created_at',
                `hits` AS 'views'
                FROM `rokh1_content` ORDER BY `id` DESC LIMIT 100;
        ";
        $result = mysqli_query($jconn, $jsql);

        if (mysqli_num_rows($result) > 0) {

            // output data of each row
            while($row = mysqli_fetch_assoc($result)) {

                $id = $row['id'];
                $post_author = $row['author'];
                $category = $row['category'];
                $post_date = $row['created_at'];
                $post_date_gmt = $row['created_at'];
                $post_content =  $row['content'];
                $post_title =  $row['title'];
                $post_excerpt = $row['lead'];
                $post_status = 'publish';
                $comment_status = 'open';
                // $post_name = sanitize_title_with_dashes_long($row['title']);
                $post_name = sanitize_title_with_dashes($row['title']);
                $guid = $env['wordpress_domain'] . "?p=$id";

                $wsql = "INSERT INTO `wp_posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES
                                                ('$id', '$post_author', '$post_date', '$post_date_gmt', '\"$post_content\"', '$post_title', '$post_excerpt', '$post_status', 'open', 'open', '', '$post_name', '', '', '$post_date', '$post_date', '', '0', '$guid', '0', 'post', '', '0');";
                $wsql_term_relat = "INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES ('$id', '$category', '0');";

                if (mysqli_query($wconn, $wsql)) {
                    echo "<pre>New article [$post_title] created successfully</pre>";
                    if (mysqli_query($wconn, $wsql_term_relat)) {
                        echo "<pre>Category assigned to article</pre>";
                    } else {
                        echo "Error: " . $wsql_term_relat . "<br>" . mysqli_error($wconn);
                    }
                } else {
                    echo "Error: " . $wsql . "<br>" . mysqli_error($wconn);
                }
            }
        } else {
            echo "<pre>function: move_articles(joomla) -> <span style='color: #f5a200; font-weight: bold;'>0 results fetched from joomla database.</span></pre>";
        }

        mysqli_close($jconn);
    }

    function move_categories($env) {
        $jconn = make_handle($env);
        $wconn = make_handle($env, 'wordpress');

        $jsql = "
            SELECT
            `id`,
            `title`,
            `alias` AS 'slug',
            `parent_id` AS 'parent',
            `path` AS 'slug'
            FROM `rokh1_categories` WHERE path != 'uncategorised' && id > 1;
        ";
        $result = mysqli_query($jconn, $jsql);

        if (mysqli_num_rows($result) > 0) {

            // output data of each row
            while($row = mysqli_fetch_assoc($result)) {
                $id = $row['id'];
                $name = $row['title'];
                $slug = str_replace('news/', '', $row['slug']);
                $parent = $row['parent'];
                $wsql = "INSERT INTO `wp_terms` (`term_id`, `name`, `slug`, `term_group`) VALUES ('$id', '$name', '$slug', '0');";
                $wsql_term = "INSERT INTO `wp_term_taxonomy` (`term_taxonomy_id`, `term_id`, `taxonomy`, `description`, `parent`, `count`) VALUES ('$id', '$id', 'category', '', '0', '0');";

                if (mysqli_query($wconn, $wsql)) {
                    echo "<pre>New category [$name] created successfully.</pre>";
                    if (mysqli_query($wconn, $wsql_term)) {
                        echo "<pre>Category [$name] taxed successfully.</pre>";
                    }
                } else {
                    echo "Error: " . $wsql . "<br>" . mysqli_error($wconn);
                }
                echo '</pre>';
            }
        } else {
            echo "<pre>function: move_categories(joomla) -> <span style='color: #f5a200; font-weight: bold;'>0 results fetched from joomla database.</span></pre>";
        }

        mysqli_close($jconn);
    }

    move_categories($env);
    move_articles($env);