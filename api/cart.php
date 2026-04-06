<?php
/**
 * API: Cart Management
 * Handles add, update, and remove operations for shopping cart
 */

session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isCustomerLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to manage your cart'
    ]);
    exit;
}

$user_id = currentCustomerId();

// Get request method and action
$action = $_POST['action'] ?? '';
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate product ID
if (!$product_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product ID'
    ]);
    exit;
}

// Validate quantity
if ($quantity < 1) {
    $quantity = 1;
}

try {
    switch ($action) {
        case 'add':
            // Check if product exists and is active
            $stmt = $conn->prepare("SELECT id, name, price, stock, status FROM products WHERE id = ?");
            $stmt->bind_param('i', $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            $stmt->close();

            if (!$product) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Product not found'
                ]);
                exit;
            }

            if ($product['status'] !== 'active') {
                echo json_encode([
                    'success' => false,
                    'message' => 'This product is not available'
                ]);
                exit;
            }

            if ($product['stock'] < 1) {
                echo json_encode([
                    'success' => false,
                    'message' => 'This product is out of stock'
                ]);
                exit;
            }

            // Check if requested quantity is available
            if ($quantity > $product['stock']) {
                echo json_encode([
                    'success' => false,
                    'message' => "Only {$product['stock']} items available in stock"
                ]);
                exit;
            }

            // Check if product already in cart
            $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param('ii', $user_id, $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            $stmt->close();

            if ($existing) {
                // Update quantity
                $new_quantity = $existing['quantity'] + $quantity;
                
                // Check if new quantity exceeds stock
                if ($new_quantity > $product['stock']) {
                    echo json_encode([
                        'success' => false,
                        'message' => "Cannot add more. Only {$product['stock']} items available in stock"
                    ]);
                    exit;
                }

                $stmt = $conn->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param('iii', $new_quantity, $user_id, $product_id);
                $stmt->execute();
                $stmt->close();

                // Log activity
                logActivity($conn, 'customer', $user_id, 'UPDATE_CART', "Updated cart: {$product['name']} (Quantity: {$new_quantity})");

                echo json_encode([
                    'success' => true,
                    'message' => 'Cart updated successfully',
                    'action' => 'updated',
                    'quantity' => $new_quantity
                ]);
            } else {
                // Insert new cart item
                $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param('iii', $user_id, $product_id, $quantity);
                $stmt->execute();
                $stmt->close();

                // Log activity
                logActivity($conn, 'customer', $user_id, 'ADD_TO_CART', "Added to cart: {$product['name']} (Quantity: {$quantity})");

                echo json_encode([
                    'success' => true,
                    'message' => 'Product added to cart successfully',
                    'action' => 'added',
                    'quantity' => $quantity
                ]);
            }
            break;

        case 'update':
            // Update cart item quantity
            if ($quantity < 1) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Quantity must be at least 1'
                ]);
                exit;
            }

            // Check product stock
            $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->bind_param('i', $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            $stmt->close();

            if (!$product) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Product not found'
                ]);
                exit;
            }

            if ($quantity > $product['stock']) {
                echo json_encode([
                    'success' => false,
                    'message' => "Only {$product['stock']} items available in stock"
                ]);
                exit;
            }

            $stmt = $conn->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param('iii', $quantity, $user_id, $product_id);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            if ($affected > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cart updated successfully',
                    'quantity' => $quantity
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cart item not found'
                ]);
            }
            break;

        case 'remove':
            // Remove item from cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param('ii', $user_id, $product_id);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            if ($affected > 0) {
                // Log activity
                logActivity($conn, 'customer', $user_id, 'REMOVE_FROM_CART', "Removed from cart: Product ID {$product_id}");

                echo json_encode([
                    'success' => true,
                    'message' => 'Item removed from cart'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cart item not found'
                ]);
            }
            break;

        case 'clear':
            // Clear entire cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->close();

            // Log activity
            logActivity($conn, 'customer', $user_id, 'CLEAR_CART', 'Cleared shopping cart');

            echo json_encode([
                'success' => true,
                'message' => 'Cart cleared successfully'
            ]);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
