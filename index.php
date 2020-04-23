<?php
    define( 'ABSPATH', dirname(dirname(__FILE__)) . '/' );
    define( 'WPINC', 'wp-includes' );
    require 'wp-functions.php';
    $env = require 'env.php';
   
    $req = "http://localhost/pure-wp/motorcycle/2-uncategorised/518-%D8%AF%D9%88%D9%84%D8%AA-%D9%86%D8%A8%D8%A7%DB%8C%D8%AF-%D8%A8%D9%87-%D8%AF%D9%86%D8%A8%D8%A7%D9%84-%D8%AA%D8%B5%D8%AF%DB%8C-%DA%AF%D8%B1%DB%8C-%D8%AF%D8%B1-%D8%B5%D9%86%D8%B9%D8%AA-%D8%AE%D9%88%D8%AF%D8%B1%D9%88-%D8%A8%D8%A7%D8%B4%D8%AF";

    if(strlen($req) > (200+strlen($env['wordpress_domain']))) {
        echo "max len: " . (200 + strlen($env['wordpress_domain'])) . ", request len: " . strlen($req) . "<hr/>";

        // remove domain base to reach pure request
        $req = str_ireplace($env['wordpress_domain'], '', ($req));

        // trim for slashes
        $req = trim($req, '/');

        // find position of id
        $dash_pos = strpos($req, '-%');

        // just keep category & id
        $article_id = substr($req, 0, $dash_pos);

        // locate last slash
        $slash_pos = strrpos($req, '/');

        // keep just id!
        $id = substr($article_id, $slash_pos);
        $id = trim($id, '/');

        echo $req;
        echo "<hr/>";
        echo $dash_pos;
        echo "<hr/>";
        echo $article_id;
        echo "<hr/>";
        echo $slash_pos;
        echo "<hr/>";
        echo "<a target='_blank' href='" . $env['wordpress_domain'] . "?p=$id" . "'>بازدید</a>";
    }




    