<?php

/**
 * This page is visible at http://localhost/contact.php
 * 
 * PHP has several global variables that are available in all scopes.
 * 
 * 
 * $_SERVER is a PHP superglobal array that holds information about the server, the request, and the current script. It includes details like the client’s IP address, request method, headers, and file paths. Developers use it to access environment data such as URLs, server names, and execution settings.
 * 
 * Which $_SERVER variable can we use to determine the request method?
 * 
 * TODO: 
 *  In the if statement, use the appropriate $_SERVER variable to check if the request method is POST.
 *  If it is, process the form submission.
 *  If it is not, display the contact form.
 *  Identify where the post data is set and displayed.
 *  Use `var_dump()` and `die()` to inspect contents of $_POST.
 *  Note: Use of `htmlspecialchars`. Research why this is used here when displaying user supplied data in the browser.
 */

if (FALSE) {
    // Collect and sanitize input
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    // Basic validation
    if ($name && $email && $message && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Here you could send an email or save the data to a database
?>
        <h2>Thank you, <?php echo $name; ?>!</h2>
        <p>Your message has been received:</p>
        <blockquote><?php echo nl2br($message); ?></blockquote>
        <p>We'll contact you at <strong><?php echo $email; ?></strong> soon.</p>
    <?php
    } else {
    ?>
        <p style='color:red;'>Please fill in all fields with valid information.</p>
    <?php
    }
    ?>
    <p><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">Back to form</a></p>
<?php
} else {

?>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="message">Message:</label><br>
        <textarea id="message" name="message" rows="5" cols="30" required></textarea><br><br>

        <button type="submit">Send</button>
    </form>
<?php
}
