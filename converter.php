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
    function mbstring_binary_safe_encoding( $reset = false ) {
        static $encodings  = array();
        static $overloaded = null;
    
        if ( is_null( $overloaded ) ) {
            $overloaded = function_exists( 'mb_internal_encoding' ) && ( ini_get( 'mbstring.func_overload' ) & 2 );
        }
    
        if ( false === $overloaded ) {
            return;
        }
    
        if ( ! $reset ) {
            $encoding = mb_internal_encoding();
            array_push( $encodings, $encoding );
            mb_internal_encoding( 'ISO-8859-1' );
        }
    
        if ( $reset && $encodings ) {
            $encoding = array_pop( $encodings );
            mb_internal_encoding( $encoding );
        }
    }
    
    /**
     * Reset the mbstring internal encoding to a users previously set encoding.
     *
     * @see mbstring_binary_safe_encoding()
     *
     * @since 3.7.0
     */
    function reset_mbstring_encoding() {
        mbstring_binary_safe_encoding( true );
    }
    
    require 'formatting.php';

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
                FROM `rokh1_content` ORDER BY `id` DESC LIMIT 2;
        ";
        $result = mysqli_query($jconn, $jsql);

        if (mysqli_num_rows($result) > 0) {

            // output data of each row
            while($row = mysqli_fetch_assoc($result)) {
                $id = $row['id'];
                $post_author = $row['author'];
                $post_date = $row['created_at'];
                $post_date_gmt = $row['created_at'];
                $post_content =  $row['content'];
                $post_title =  $row['title'];
                $post_excerpt = $row['lead'];
                $post_status = 'publish';
                $comment_status = 'open';
                // $post_name = urlencode($row['joomla_slug']);
                $post_name = sanitize_title_with_dashes($row['title']);
                $guid = $env['wordpress_domain'] . "?p=$id";
                $wsql = "INSERT INTO `wp_posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES
                                                ('$id', '$post_author', '$post_date', '$post_date_gmt', '$post_content', '$post_title', '$post_excerpt', '$post_status', 'open', 'open', '', '$post_name', '', '', '$post_date', '$post_date', '', '0', '$guid', '0', 'post', '', '0');";

                if (mysqli_query($wconn, $wsql)) {
                    echo "<pre>New record created successfully</pre>";
                } else {
                    echo "Error: " . $wsql . "<br>" . mysqli_error($wconn);
                }
                echo '</pre>';
            }
        } else {
            echo "<pre>function: make_handle(joomla) -> <span style='color: #f5a200; font-weight: bold;'>0 results fetched from joomla database.</span></pre>";
        }

        mysqli_close($jconn);
    }

    move_articles($env);