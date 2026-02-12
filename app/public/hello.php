<?php

/**
 * PHP has several global variables that are available in all scopes.
 * 
 * $_GET is one of those global variables. It is an associative array of variables passed to the current script via the URL query parameters.
 * 
 * For example, if a user visits the following URL:
 * 
 * http://localhost/hello.php?name=Alice&age=23
 * 
 * Two key-value pairs are sent to the server: one for `name` with the value `Alice`, and another for `age` with the value `23`.
 * 
 * Note: PHP has a `var_dump` function that can be used to inspect variables.
 * 
 * TODO:
 *  Visit the URL above in the browser.
 *  Use `var_dump($_GET);` to see the contents of the `$_GET` variable. 
 *  Use `die();` after the `var_dump` to stop further execution of the script.
 *  Right-click in the browser and select "View Page Source" to see the output of `var_dump` clearly.
 */

/** 
 * After inspecting the `$_GET` variable, remove the `var_dump` and `die` statements and do the following:
 *  Use the `name` and `age` parameter from the `$_GET` variable to personalize the greeting message.
 *  The message should say something like: "Hello Alice, you are 23 years old!"
 *  Provide fallback handling if the parameters are missing, e.g., "Hello Guest, I don't know your age!"
 * 
 */

// Note the `echo` outputs text to the HTTP response body directly (you will see this in the browser).
echo 'Hello world!';

// Note: PHP is a templating language, so you can mix HTML and PHP code in the same file.
// To start writing HTML, just close the PHP tag like this:

// View source code in the browser to see the HTML output. Anything missing? 😱😱😱

?>

<div>
    <iframe src="https://giphy.com/embed/xTk9ZY0C9ZWM2NgmCA" width="480" height="480" style="" frameBorder="0"
        class="giphy-embed" allowFullScreen></iframe>
</div>