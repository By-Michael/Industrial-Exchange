<?php

require_once 'database.php';


session_start();

// Enable verbose errors during local development
error_reporting(E_ALL);
ini_set('display_errors', '1');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';

function sendOTP($email, $otp) {
    global $SMTP_HOST, $SMTP_PORT, $SMTP_USERNAME, $SMTP_PASSWORD, $SMTP_FROM_EMAIL, $SMTP_FROM_NAME;
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = $SMTP_USERNAME;
        $mail->Password   = $SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $SMTP_PORT;

        $mail->setFrom($SMTP_FROM_EMAIL, $SMTP_FROM_NAME);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code';
        $mail->Body    = "Your 6-digit verification code is: <b>$otp</b>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $e->getMessage());
        return false;
    }
}

function esc($text) {
    return htmlspecialchars($text);
}

function showMsg($message, $type = "info") {
    $_SESSION["msg"] = "<div class='alert alert-$type'>$message</div>";
}

// Messaging helpers
function getProductMessages($product_id, $current_user_id) {
    global $conn;
    $pid = intval($product_id);
    $uid = intval($current_user_id);
    $query = "SELECT m.*, 
              u_sender.industry_name as sender_name,
              u_receiver.industry_name as receiver_name
              FROM messages m
              JOIN users u_sender ON m.sender_id = u_sender.id
              JOIN users u_receiver ON m.receiver_id = u_receiver.id
              WHERE m.product_id = '$pid'
              AND (m.sender_id = '$uid' OR m.receiver_id = '$uid')
              ORDER BY m.created_at ASC";
    return $conn->query($query);
}

function getMyConversations($user_id) {
    global $conn;
    $uid = intval($user_id);
    $query = "SELECT DISTINCT p.id as product_id, p.product_name, 
              CASE WHEN m.sender_id = '$uid' THEN u_receiver.industry_name ELSE u_sender.industry_name END as other_user_name,
              MAX(m.created_at) as last_message_time
              FROM messages m
              JOIN products p ON m.product_id = p.id
              LEFT JOIN users u_sender ON m.sender_id = u_sender.id
              LEFT JOIN users u_receiver ON m.receiver_id = u_receiver.id
              WHERE m.sender_id = '$uid' OR m.receiver_id = '$uid'
              GROUP BY p.id
              ORDER BY last_message_time DESC";
    return $conn->query($query);
}


if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: ?");
    exit;
}


$action = "market";
if (isset($_GET["action"])) {
    $action = $_GET["action"];
} elseif (!isset($_SESSION["uid"])) {
    $action = "login";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    if (isset($_POST["signup"])) {
        $industry_name = $conn->real_escape_string($_POST["industry_name"] ?? '');
        $email = $conn->real_escape_string($_POST["email"] ?? '');
        $password = $_POST["password"] ?? '';
        $confirm_password = $_POST["confirm_password"] ?? '';

        if ($password !== $confirm_password) {
            showMsg("Passwords do not match", "danger");
        } else {
            $result = $conn->query("SELECT id FROM users WHERE email='$email'");
            if ($result && $result->num_rows > 0) {
                showMsg("Email already exists", "danger");
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $conn->query("INSERT INTO users (industry_name, email, password, otp_code, is_verified) VALUES ('$industry_name', '$email', '$hash', NULL, 1)");
                if ($ins) {
                    $_SESSION["uid"] = $conn->insert_id;
                    $_SESSION["uname"] = $industry_name;
                    showMsg("Account created", "success");
                    header("Location: ?action=market");
                    exit;
                } else {
                    showMsg("Failed to create account: " . $conn->error, "danger");
                }
            }
        }
    }
    

    if (isset($_POST["login"])) {
        $email = $conn->real_escape_string($_POST["email"] ?? '');
        $password = $_POST["password"] ?? '';

        $result = $conn->query("SELECT * FROM users WHERE email='$email'");
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row["password"])) {
                if (!empty($row['is_verified']) && $row['is_verified'] == 1) {
                    $_SESSION["uid"] = $row["id"];
                    $_SESSION["uname"] = $row["industry_name"];
                    showMsg("Welcome back, " . $row["industry_name"] . "!", "success");
                    header("Location: ?action=market");
                    exit;
                } else {
                    showMsg("Invalid email or password", "danger");
                    $action = "login";
                }
            } else {
                showMsg("Invalid email or password", "danger");
            }
        } else {
            showMsg("Invalid email or password", "danger");
        }
    }
    
 
    if (isset($_POST["add_product"]) and isset($_SESSION["uid"])) {
        $user_id = $_SESSION["uid"];
        $name = $_POST["name"];
        $category = $_POST["category"];
        $quantity = $_POST["quantity"];
        $unit = $_POST["unit"];
        $condition = $_POST["condition"];
        $location = $_POST["location"];
        $price = $_POST["price"];
        $description = $_POST["description"];
        
        $conn->query("INSERT INTO products (user_id, product_name, category, quantity, unit, condition_status, location, price, description) 
                     VALUES ('$user_id', '$name', '$category', '$quantity', '$unit', '$condition', '$location', '$price', '$description')");
        showMsg("Product added successfully!", "success");
        $action = "market";
    }
    
    
    if (isset($_POST["edit_product"]) and isset($_SESSION["uid"])) {
        $pid = $_POST["pid"];
        $name = $_POST["name"];
        $category = $_POST["category"];
        $quantity = $_POST["quantity"];
        $unit = $_POST["unit"];
        $condition = $_POST["condition"];
        $location = $_POST["location"];
        $price = $_POST["price"];
        $description = $_POST["description"];
        $status = $_POST["status"];
        $user_id = $_SESSION["uid"];
        
        $conn->query("UPDATE products SET 
                     product_name='$name', 
                     category='$category', 
                     quantity='$quantity', 
                     unit='$unit', 
                     condition_status='$condition', 
                     location='$location', 
                     price='$price', 
                     description='$description',
                     status='$status'
                     WHERE id='$pid' AND user_id='$user_id'");
        showMsg("Product updated successfully!", "success");
        $action = "market";
    }

    // Profile update (industry name, email)
    if (isset($_POST["update_profile"]) and isset($_SESSION["uid"])) {
        $uid = $_SESSION["uid"];
        $industry_name = $conn->real_escape_string($_POST["industry_name"] ?? '');
        $email = $conn->real_escape_string($_POST["email"] ?? '');

        $check = $conn->query("SELECT id FROM users WHERE email='$email' AND id<>'$uid'");
        if ($check && $check->num_rows > 0) {
            showMsg("Email already in use by another account", "danger");
            $action = "profile";
        } else {
            $sql = "UPDATE users SET industry_name='$industry_name', email='$email' WHERE id='$uid'";
            if (!$conn->query($sql)) {
                showMsg("Failed to update profile: " . $conn->error, "danger");
            } else {
                $_SESSION["uname"] = $industry_name;
                showMsg("Profile updated successfully", "success");
            }
            $action = "profile";
        }
    }

    // Change password (while logged in)
    if (isset($_POST["change_password"]) and isset($_SESSION["uid"])) {
        $uid = $_SESSION["uid"];
        $current = $_POST["current_password"] ?? '';
        $new = $_POST["new_password"] ?? '';
        $res = $conn->query("SELECT password FROM users WHERE id='$uid'");
        if ($row = $res->fetch_assoc()) {
            if (password_verify($current, $row['password'])) {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $conn->query("UPDATE users SET password='$hash' WHERE id='$uid'");
                showMsg("Password changed successfully", "success");
            } else {
                showMsg("Current password is incorrect", "danger");
            }
        }
        $action = "profile";
    }

    // Initiate password recovery via email OTP
    if (isset($_POST["send_recovery_otp"])) {
        $email = $conn->real_escape_string($_POST["email"] ?? '');
        $res = $conn->query("SELECT id FROM users WHERE email='$email'");
        if ($row = $res->fetch_assoc()) {
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            if ($conn->query("UPDATE users SET otp_code='$otp' WHERE email='$email'")) {
                if (sendOTP($email, $otp)) {
                    $_SESSION['recover_temp_email'] = $email;
                    showMsg("A verification code was sent to your email.", "success");
                    $action = "verify_recover_otp";
                } else {
                    showMsg("Failed to send verification code. Try again later.", "danger");
                    $action = "recover";
                }
            } else {
                showMsg("Failed to prepare recovery code: " . $conn->error, "danger");
                $action = "recover";
            }
        } else {
            showMsg("Email not found", "danger");
            $action = "recover";
        }
    }

    // Verify OTP sent for password recovery
    if (isset($_POST["verify_recover_otp"])) {
        $user_otp = $conn->real_escape_string($_POST["otp_input"] ?? '');
        $email = $_SESSION['recover_temp_email'] ?? null;
        if (!$email) {
            showMsg("Recovery session expired. Please request a new code.", "danger");
            $action = "recover";
        } else {
            $res = $conn->query("SELECT id FROM users WHERE email='$email' AND otp_code='$user_otp'");
            if ($res && $res->num_rows > 0) {
                $_SESSION['recover_otp_verified'] = true;
                showMsg("Code verified. You may now reset your password.", "success");
                $action = "reset_password_otp";
            } else {
                showMsg("Invalid code. Please try again.", "danger");
                $action = "verify_recover_otp";
            }
        }
    }

    // Perform password reset after OTP verification
    if (isset($_POST["reset_password_otp"])) {
        if (empty($_SESSION['recover_otp_verified']) || empty($_SESSION['recover_temp_email'])) {
            showMsg("Recovery session invalid or expired.", "danger");
            $action = "recover";
        } else {
            $email = $conn->real_escape_string($_SESSION['recover_temp_email']);
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            if ($new === '' || $new !== $confirm) {
                showMsg("Passwords do not match or empty.", "danger");
                $action = "reset_password_otp";
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                if ($conn->query("UPDATE users SET password='$hash', otp_code=NULL, is_verified=1 WHERE email='$email'")) {
                    unset($_SESSION['recover_otp_verified'], $_SESSION['recover_temp_email']);
                    showMsg("Password reset successfully. You may now login.", "success");
                    $action = "login";
                } else {
                    showMsg("Failed to reset password: " . $conn->error, "danger");
                    $action = "reset_password_otp";
                }
            }
        }
    }
    
    // Send a simple message to product owner
    if (isset($_POST['send_message']) && isset($_SESSION['uid'])) {
        $product_id = intval($_POST['product_id'] ?? 0);
        $message_text = trim($conn->real_escape_string($_POST['message_text'] ?? ''));
        $sender_id = intval($_SESSION['uid']);

        if ($product_id <= 0) {
            showMsg("Invalid product", "danger");
        } elseif ($message_text === '') {
            showMsg("Message cannot be empty", "danger");
        } else {
            if (!empty($_POST['receiver_id'])) {
                $receiver_id = intval($_POST['receiver_id']);
            } else {
                $res = $conn->query("SELECT user_id FROM products WHERE id='$product_id'");
                if ($res && $prow = $res->fetch_assoc()) {
                    $receiver_id = intval($prow['user_id']);
                } else {
                    showMsg("Product not found", "danger");
                    $action = "market";
                    exit;
                }
            }

            if ($receiver_id === $sender_id) {
                showMsg("You cannot message yourself", "danger");
            } else {
                $conn->query("INSERT INTO messages (product_id, sender_id, receiver_id, message_text) VALUES ('$product_id', '$sender_id', '$receiver_id', '$message_text')");
                showMsg("Message sent!", "success");
                header("Location: ?action=view_conversation&product_id=$product_id");
                exit;
            }
        }
    }

   
}


if (isset($_GET["delete"]) and isset($_SESSION["uid"])) {
    $delete_id = $_GET["delete"];
    $user_id = $_SESSION["uid"];
    $conn->query("DELETE FROM products WHERE id='$delete_id' AND user_id='$user_id'");
    showMsg("Product deleted successfully!", "success");
    $action = "market";
}


$categories_result = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
$categories = array();
while ($cat = $categories_result->fetch_assoc()) {
    $categories[] = $cat["category"];
}

$locations_result = $conn->query("SELECT DISTINCT location FROM products ORDER BY location");
$locations = array();
while ($loc = $locations_result->fetch_assoc()) {
    $locations[] = $loc["location"];
}
?>