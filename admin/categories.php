<?php
/**
 * DEBRE TABOR GEBEYA E-Commerce System
 * Admin Category Management Page
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/categories.php';
require_once __DIR__ . '/../php/localization.php';

// Require admin login
Auth::require_role('admin');

$current_language = get_language();
$categories = Categories::get_all_categories();
$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    
    if ($action === 'add') {
        $result = Categories::add_category($name, $description);
        if ($result['success']) {
            $success_message = $result['message'];
            header('Refresh: 2; url=categories.php');
        } else {
            $error_message = $result['message'];
        }
    } elseif ($action === 'edit') {
        $result = Categories::update_category($category_id, $name, $description);
        if ($result['success']) {
            $success_message = $result['message'];
            header('Refresh: 2; url=categories.php');
        } else {
            $error_message = $result['message'];
        }
    } elseif ($action === 'delete') {
        $result = Categories::delete_category($category_id);
        if ($result['success']) {
            $success_message = $result['message'];
            header('Refresh: 2; url=categories.php');
        } else {
            $error_message = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('manage_categories', $current_language); ?> - DEBRE TABOR GEBEYA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navigation.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <div class="container" style="padding: 40px 20px;">
        <h1><?php echo translate('manage_categories', $current_language); ?></h1>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px;">
            <!-- Add Category Form -->
            <div style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
                <h3><?php echo translate('add_category', $current_language); ?></h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label for="add_name"><?php echo translate('category_name', $current_language); ?></label>
                        <input type="text" id="add_name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="add_description"><?php echo translate('description', $current_language); ?></label>
                        <textarea id="add_description" name="description"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <?php echo translate('add_category', $current_language); ?>
                    </button>
                </form>
            </div>

            <!-- Categories List -->
            <div>
                <h3><?php echo translate('existing_categories', $current_language); ?></h3>
                <?php if (empty($categories)): ?>
                    <p><?php echo translate('no_categories', $current_language); ?></p>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($categories as $cat): ?>
                            <li style="padding: 10px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                    <?php if ($cat['description']): ?>
                                        <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;">
                                            <?php echo htmlspecialchars($cat['description']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; gap: 5px;">
                                    <button class="btn btn-secondary btn-sm" onclick="editCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name']); ?>', '<?php echo htmlspecialchars($cat['description']); ?>')">
                                        <?php echo translate('edit', $current_language); ?>
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('<?php echo translate('confirm_delete_category', $current_language); ?>')">
                                            <?php echo translate('delete', $current_language); ?>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
            <div style="background: white; padding: 30px; border-radius: 8px; max-width: 400px; width: 90%;">
                <h3><?php echo translate('edit_category', $current_language); ?></h3>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="category_id" id="edit_category_id">
                    
                    <div class="form-group">
                        <label for="edit_name"><?php echo translate('category_name', $current_language); ?></label>
                        <input type="text" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_description"><?php echo translate('description', $current_language); ?></label>
                        <textarea id="edit_description" name="description"></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            <?php echo translate('update_category', $current_language); ?>
                        </button>
                        <button type="button" class="btn btn-secondary" style="flex: 1;" onclick="closeEditModal()">
                            <?php echo translate('cancel', $current_language); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="../js/localization.js"></script>
    <script>
        function editCategory(categoryId, name, description) {
            document.getElementById('edit_category_id').value = categoryId;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>
