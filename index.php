<?php
require_once 'database.php';
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Industrial Exchange Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="background-container">
        <?php if(!isset($_SESSION["uid"]) || in_array($action, ["login", "signup"])): ?>
            <div class="login-background"></div>
        <?php else: ?>
            <div class="dashboard-background"></div>
        <?php endif; ?>
    </div>
    
    <div class="nav">
        <?php if(isset($_SESSION["uid"])): ?>
            <a href="?action=market">Marketplace</a>
            <a href="?action=add">Add Product</a>
            <a href="?action=my_products">My Products</a>
            <a href="?action=my_messages">Messages</a>
            <a href="?action=profile">Profile</a>
            <a href="?logout">Logout</a>
            <span style="float: right; color: white; padding: 14px 16px;">
                Welcome, <?php echo esc($_SESSION["uname"]); ?>
            </span>
        <?php else: ?>
            <a href="?action=login">Login</a>
            <a href="?action=signup">Signup</a>
        <?php endif; ?>
    </div>

    <?php if(isset($_SESSION["msg"])): ?>
        <?php echo $_SESSION["msg"]; ?>
        <?php unset($_SESSION["msg"]); ?>
    <?php endif; ?>

    <?php if($action == "signup"): ?>
        <div class="content-overlay login-overlay">
            <h2>Create Account</h2>
            <form method="post">
                <div class="form-group">
                    <label>Industry Name</label>
                    <input type="text" name="industry_name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" name="signup">Create Account</button>
            </form>
            <p>Already have an account? <a href="?action=login">Login here</a></p>
        </div>

    <?php elseif($action == "login"): ?>
        <div class="content-overlay login-overlay">
            <h2>Login to Your Account</h2>
            <form method="post">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
            <p>New to Industrial Exchange? <a href="?action=signup">Create account</a></p>
            <p>Forgot password? <a href="?action=recover">Recover account</a></p>
        </div>

    <?php elseif($action == "profile" and isset($_SESSION["uid"])): 
        $uid = $_SESSION["uid"];
        $res = $conn->query("SELECT * FROM users WHERE id='$uid'");
        $user = $res->fetch_assoc();
    ?>
        <div class="content-overlay login-overlay">
            <h2>Profile Settings</h2>
            <form method="post">
                <div class="form-group">
                    <label>Industry Name</label>
                    <input type="text" name="industry_name" value="<?php echo esc($user['industry_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo esc($user['email'] ?? ''); ?>" required>
                </div>
                <button type="submit" name="update_profile">Update Profile</button>
            </form>

            <h3>Change Password</h3>
            <form method="post" style="margin-top:10px;">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                </div>
                <button type="submit" name="change_password">Change Password</button>
            </form>
        </div>

    <?php elseif($action == "recover"): ?>
        <div class="content-overlay login-overlay">
            <h2>Recover Account</h2>
            <form method="post">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>
                <button type="submit" name="send_recovery_otp">Send verification code to my email</button>
            </form>
        </div>

    <?php elseif($action == "verify_recover_otp"): ?>
        <div class="content-overlay login-overlay">
            <h2>Enter Recovery Code</h2>
            <p>We sent a 6-digit code to <?php echo esc($_SESSION['recover_temp_email'] ?? 'your email'); ?></p>
            <form method="post">
                <div class="form-group">
                    <input type="text" name="otp_input" placeholder="123456" required maxlength="6">
                </div>
                <button type="submit" name="verify_recover_otp">Verify Code</button>
            </form>
        </div>

    <?php elseif($action == "reset_password_otp"): ?>
        <div class="content-overlay login-overlay">
            <h2>Reset Password</h2>
            <form method="post">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" name="reset_password_otp">Reset Password</button>
            </form>
        </div>


    <?php elseif($action == "market" and isset($_SESSION["uid"])): ?>
        <div class="content-overlay">
            <h1>Industrial Marketplace</h1>
            
            <form method="get" style="margin: 20px 0;">
                <input type="hidden" name="action" value="market">
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="search" placeholder="Search products..." value="<?php echo esc($_GET["search"] ?? ""); ?>">
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo esc($cat); ?>" <?php echo ($_GET["category"] ?? "") == $cat ? "selected" : ""; ?>>
                                <?php echo esc($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="location">
                        <option value="">All Locations</option>
                        <?php foreach($locations as $loc): ?>
                            <option value="<?php echo esc($loc); ?>" <?php echo ($_GET["location"] ?? "") == $loc ? "selected" : ""; ?>>
                                <?php echo esc($loc); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Filter</button>
                </div>
            </form>

            <div style="display: flex; flex-wrap: wrap; gap: 20px;">
            <?php
            $query = "SELECT p.*, u.industry_name FROM products p JOIN users u ON p.user_id = u.id WHERE 1=1";
            
            if(isset($_GET["search"]) and !empty($_GET["search"])) {
                $search = $_GET["search"];
                $query .= " AND (p.product_name LIKE '%$search%' OR p.description LIKE '%$search%')";
            }
                if(isset($_GET["category"]) and !empty($_GET["category"])) {
                $category = $_GET["category"];
                $query .= " AND p.category = '$category'";
            }
            
            if(isset($_GET["location"]) and !empty($_GET["location"])) {
                $location = $_GET["location"];
                $query .= " AND p.location = '$location'";
            }
            
            $query .= " ORDER BY p.created_at DESC";
            $result = $conn->query($query);
            
            if($result->num_rows > 0):
                while($row = $result->fetch_assoc()):
            ?>
                <div class="card" style="width: 300px;">
                    <h3><?php echo esc($row["product_name"]); ?></h3>
                    <p><strong>Category:</strong> <?php echo esc($row["category"]); ?></p>
                    <p><strong>Quantity:</strong> <?php echo esc($row["quantity"]); ?> <?php echo esc($row["unit"]); ?></p>
                    <p><strong>Condition:</strong> <?php echo esc($row["condition_status"]); ?></p>
                    <p><strong>Location:</strong> <?php echo esc($row["location"]); ?></p>
                    <p><strong>Price:</strong> ETB<?php echo esc($row["price"]); ?></p>
                    <p><strong>Status:</strong> <?php echo esc($row["status"]); ?></p>
                    <p><strong>Seller:</strong> <?php echo esc($row["industry_name"]); ?></p>
                    
                    <?php if($row["user_id"] == $_SESSION["uid"]): ?>
                        <div>
                            <button type="button" class="btn-small btn-edit" onclick="location.href='?action=edit&id=<?php echo $row["id"]; ?>'">Edit</button>
                            <button type="button" class="btn-small btn-delete" onclick="if(confirm('Are you sure?')) location.href='?delete=<?php echo $row["id"]; ?>'">Delete</button>
                            <button type="button" class="btn-small" onclick="location.href='?action=view_conversation&product_id=<?php echo $row['id']; ?>'">View Messages</button>
                        </div>
                    <?php else: ?>
                        <div>
                            <button type="button" class="btn-small btn-message" onclick="location.href='?action=view_conversation&product_id=<?php echo $row["id"]; ?>'">💬 Message Seller</button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <p>No products found.</p>
            <?php endif; ?>
            </div>
        </div>

    <?php elseif($action == "add" and isset($_SESSION["uid"])): ?>
        <div class="content-overlay login-overlay">
            <h2>Add New Product</h2>
            <form method="post">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="quantity" required>
                </div>
                <div class="form-group">
                    <label>Unit</label>
                    <select name="unit" required>
                        <option value="kg">Kilogram (kg)</option>
                        <option value="ton">Ton</option>
                        <option value="liter">Liter</option>
                        <option value="piece">Piece</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Condition</label>
                    <select name="condition" required>
                        <option value="New">New</option>
                        <option value="Used - Good">Used - Good</option>
                        <option value="Refurbished">Refurbished</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" required>
                </div>
                <div class="form-group">
                    <label>Price (ETB)</label>
                    <input type="number" step="0.01" name="price">
                </div>
                <button type="submit" name="add_product">Add Product</button>
                <button type="button" class="btn-small btn-cancel" onclick="location.href='?action=market'">Cancel</button>
            </form>
        </div>

    <?php elseif($action == "edit" and isset($_SESSION["uid"]) and isset($_GET["id"])): 
        $id = $_GET["id"];
        $user_id = $_SESSION["uid"];
        $result = $conn->query("SELECT * FROM products WHERE id='$id' AND user_id='$user_id'");
        if($row = $result->fetch_assoc()):
    ?>
        <div class="content-overlay login-overlay">
            <h2>Edit Product</h2>
            <form method="post">
                <input type="hidden" name="pid" value="<?php echo $row["id"]; ?>">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" value="<?php echo esc($row["product_name"]); ?>" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" value="<?php echo esc($row["category"]); ?>" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"><?php echo esc($row["description"]); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="quantity" value="<?php echo esc($row["quantity"]); ?>" required>
                </div>
                <div class="form-group">
                    <label>Unit</label>
                    <select name="unit" required>
                        <option value="kg" <?php echo $row["unit"]=="kg"?"selected":""; ?>>Kilogram (kg)</option>
                        <option value="ton" <?php echo $row["unit"]=="ton"?"selected":""; ?>>Ton</option>
                        <option value="liter" <?php echo $row["unit"]=="liter"?"selected":""; ?>>Liter</option>
                        <option value="piece" <?php echo $row["unit"]=="piece"?"selected":""; ?>>Piece</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Condition</label>
                    <select name="condition" required>
                        <option value="New" <?php echo $row["condition_status"]=="New"?"selected":""; ?>>New</option>
                        <option value="Used - Good" <?php echo $row["condition_status"]=="Used - Good"?"selected":""; ?>>Used - Good</option>
                        <option value="Refurbished" <?php echo $row["condition_status"]=="Refurbished"?"selected":""; ?>>Refurbished</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?php echo esc($row["location"]); ?>" required>
                </div>
                <div class="form-group">
                    <label>Price (ETB)</label>
                    <input type="number" step="0.01" name="price" value="<?php echo esc($row["price"]); ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="Available" <?php echo $row["status"]=="Available"?"selected":""; ?>>Available</option>
                        <option value="Sold" <?php echo $row["status"]=="Sold"?"selected":""; ?>>Sold</option>
                    </select>
                </div>
                <button type="submit" name="edit_product">Update Product</button>
                <button type="button" class="btn-small btn-cancel" onclick="location.href='?action=market'">Cancel</button>
            </form>
        </div>
    <?php else: ?>
        <div class="content-overlay">
            <p>Product not found or you don't have permission to edit it.</p>
        </div>
    <?php endif; ?>

    <?php elseif($action == "my_messages" and isset($_SESSION["uid"])): 
        $user_id = $_SESSION["uid"];
        $conversations = getMyConversations($user_id);
    ?>
        <div class="content-overlay">
            <h2>My Conversations</h2>
            
            <?php if($conversations && $conversations->num_rows > 0): ?>
                <div class="conversation-list">
                    <?php while($conv = $conversations->fetch_assoc()): ?>
                        <div class="conversation-item" style="border:1px solid #ddd; padding:15px; margin-bottom:15px; border-radius:5px; background:white;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <h4 style="margin:0;"><?php echo esc($conv['product_name']); ?></h4>
                                    <p style="margin:5px 0 0 0; color:#666;">
                                        Conversation with: <strong><?php echo esc($conv['other_user_name']); ?></strong>
                                    </p>
                                </div>
                                <div>
                                    <button class="btn-small" 
                                            onclick="location.href='?action=view_conversation&product_id=<?php echo $conv['product_id']; ?>'">
                                        Open Chat
                                    </button>
                                </div>
                            </div>
                            <small style="color:#999; display:block; margin-top:10px;">
                                Last activity: <?php echo date('M d, H:i', strtotime($conv['last_message_time'])); ?>
                            </small>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="text-align:center; padding:40px;">
                    You have no conversations yet.<br>
                    <a href="?action=market">Browse marketplace</a> to start messaging sellers.
                </p>
            <?php endif; ?>
            
            <div style="margin-top:20px; text-align:center;">
                <button class="btn-small" onclick="location.href='?action=market'">Back to Marketplace</button>
            </div>
        </div>

    <?php elseif($action == "view_conversation" && isset($_SESSION["uid"]) && isset($_GET["product_id"])):
        $product_id = intval($_GET["product_id"]);
        $current_user_id = $_SESSION["uid"];
        
        $product_query = $conn->query("SELECT p.*, u.industry_name as seller_name, p.user_id FROM products p JOIN users u ON p.user_id = u.id WHERE p.id='$product_id'");
        
        if($product_query && $product = $product_query->fetch_assoc()):
            $messages = getProductMessages($product_id, $current_user_id);
            
            $other_user_query = $conn->query("SELECT DISTINCT 
                                              CASE 
                                                  WHEN sender_id = '$current_user_id' THEN receiver_id
                                                  ELSE sender_id
                                              END as other_user_id,
                                              u.industry_name as other_user_name
                                              FROM messages m
                                              JOIN users u ON (
                                                  CASE 
                                                      WHEN m.sender_id = '$current_user_id' THEN m.receiver_id
                                                      ELSE m.sender_id
                                                  END
                                              ) = u.id
                                              WHERE m.product_id = '$product_id'
                                              LIMIT 1");
            
            if($other_user_query && $other_user = $other_user_query->fetch_assoc()) {
                $other_user_id = $other_user['other_user_id'];
                $other_user_name = $other_user['other_user_name'];
            } else {
                $other_user_id = $product['user_id'];
                $other_user_query = $conn->query("SELECT industry_name FROM users WHERE id='$other_user_id'");
                $other_user = $other_user_query->fetch_assoc();
                $other_user_name = $other_user['industry_name'];
            }
    ?>
        <div class="content-overlay">
            <div style="background:#f8f9fa; padding:15px; border-radius:5px; margin-bottom:20px;">
                <h3 style="margin:0 0 10px 0;">
                    Chat about: <?php echo esc($product['product_name']); ?>
                </h3>
                <div style="display:flex; flex-wrap:wrap; gap:10px; font-size:14px;">
                    <span><strong>Price:</strong> ETB<?php echo number_format($product['price'], 2); ?></span>
                    <span><strong>Status:</strong> <?php echo esc($product['status']); ?></span>
                    <span><strong>With:</strong> <?php echo esc($other_user_name); ?></span>
                </div>
            </div>
            
            <div class="chat-box" style="border:1px solid #ddd; border-radius:5px; padding:15px; 
                  height:400px; overflow-y:auto; margin-bottom:20px; background:#f9f9f9;">
                
                <?php if($messages && $messages->num_rows > 0): ?>
                    <?php while($msg = $messages->fetch_assoc()): 
                        $is_me = ($msg['sender_id'] == $current_user_id);
                    ?>
                        <div style="margin-bottom:15px; clear:both;">
                            <div style="float:<?php echo $is_me ? 'right' : 'left'; ?>; 
                                 max-width:70%; background:<?php echo $is_me ? '#d4edda' : 'white'; ?>; 
                                 padding:10px; border-radius:10px; border:1px solid #ddd;">
                                <div style="font-size:12px; color:#666; margin-bottom:5px;">
                                    <strong><?php echo esc($msg['sender_name']); ?></strong>
                                    <span style="margin-left:10px;"><?php echo date('H:i', strtotime($msg['created_at'])); ?></span>
                                </div>
                                <div style="word-wrap:break-word;">
                                    <?php echo nl2br(esc($msg['message_text'])); ?>
                                </div>
                            </div>
                            <div style="clear:both;"></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align:center; padding:40px; color:#666;">
                        No messages yet. Start the conversation!
                    </div>
                <?php endif; ?>
            </div>
            
            <form method="post" style="background:white; padding:15px; border-radius:5px; border:1px solid #ddd;">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="receiver_id" value="<?php echo $other_user_id; ?>">
                <input type="hidden" name="is_reply" value="1">
                
                <div style="display:flex; gap:10px;">
                    <textarea name="message_text" rows="2" 
                              placeholder="Type your message here..." 
                              style="flex:1; padding:10px; border:1px solid #ddd; border-radius:5px;" 
                              required></textarea>
                    <button type="submit" name="send_message" style="align-self:flex-end; padding:10px 20px;">
                        Send
                    </button>
                </div>
            </form>
            
            <div style="margin-top:20px; display:flex; gap:10px; justify-content:center;">
                <button class="btn-small" onclick="location.href='?action=market'">
                    Back to Marketplace
                </button>
                <button class="btn-small" onclick="location.href='?action=my_messages'">
                    My Conversations
                </button>
            </div>
        </div>
        
        <script>
        window.onload = function() {
            var chatBox = document.querySelector('.chat-box');
            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }
        </script>
    <?php else: ?>
        <div class="content-overlay">
            <p>Conversation not found.</p>
            <button class="btn-small" onclick="location.href='?action=market'">Back to Marketplace</button>
        </div>
    <?php endif; ?>

    <?php elseif($action == "my_products" and isset($_SESSION["uid"])): ?>
        <div class="content-overlay">
            <h2>My Products</h2>
            <table>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Condition</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php
                $user_id = $_SESSION["uid"];
                $result = $conn->query("SELECT * FROM products WHERE user_id='$user_id' ORDER BY created_at DESC");
                while($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo esc($row["product_name"]); ?></td>
                    <td><?php echo esc($row["category"]); ?></td>
                    <td><?php echo esc($row["quantity"]); ?> <?php echo esc($row["unit"]); ?></td>
                    <td><?php echo esc($row["condition_status"]); ?></td>
                    <td>ETB<?php echo number_format($row["price"]); ?></td>
                    <td><?php echo esc($row["status"]); ?></td>
                    <td>
                        <button type="button" class="btn-small btn-edit" onclick="location.href='?action=edit&id=<?php echo $row["id"]; ?>'">Edit</button>
                        <button type="button" class="btn-small btn-delete" onclick="if(confirm('Are you sure?')) location.href='?delete=<?php echo $row["id"]; ?>'">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        
    <?php else: ?>
        <div class="content-overlay login-overlay">
            <h1>Welcome to Industrial Exchange Platform</h1>
            <p>Please <a href="?action=login">login</a> or <a href="?action=signup">create an account</a> to continue.</p>
        </div>
    <?php endif; ?>

    

</body>
</html>