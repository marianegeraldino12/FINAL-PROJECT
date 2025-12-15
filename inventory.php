<?php
session_start();
if (!isset($_SESSION['user'])) header('Location: index.php');
include "backend/db.php";

// 1. Kumuha ng Summary Data para sa Cards (BAGO)
// Total Item (Unique rows)
$total_item_query = "SELECT COUNT(id) AS total_item FROM inventory";
$total_item_result = $conn->query($total_item_query);
$total_item = $total_item_result->fetch_assoc()['total_item'];

// Total Quantity (Sum ng lahat ng quantity)
$total_quantity_query = "SELECT SUM(quantity) AS total_quantity FROM inventory";
$total_quantity_result = $conn->query($total_quantity_query);
$total_quantity = $total_quantity_result->fetch_assoc()['total_quantity'] ?? 0;

// Total Categories (Distinct categories)
$total_categories_query = "SELECT COUNT(DISTINCT category) AS total_categories FROM inventory";
$total_categories_result = $conn->query($total_categories_query);
$total_categories = $total_categories_result->fetch_assoc()['total_categories'];

// Need Attention (Halimbawa: Condition = 'fail')
$need_attention_query = "SELECT COUNT(id) AS need_attention FROM inventory WHERE cond = 'fail'";
$need_attention_result = $conn->query($need_attention_query);
$need_attention = $need_attention_result->fetch_assoc()['need_attention'];


// 2. Pagination at Filtering Logic (Katulad ng Dati)
$items_per_page = 8;
$current_page = $_GET['page'] ?? 1;
$offset = ($current_page - 1) * $items_per_page;

$search = $_GET['search'] ?? '';
$category_filter = $_GET['category_filter'] ?? '';
$condition_filter = $_GET['condition_filter'] ?? '';

$sql = "SELECT * FROM inventory";
$conditions = [];

if (!empty($search)) {
    $search_term = "%" . $conn->real_escape_string($search) . "%";
    $conditions[] = "(item LIKE '$search_term' OR category LIKE '$search_term' OR cond LIKE '$search_term' OR location LIKE '$search_term')";
}

if (!empty($category_filter)) {
    $conditions[] = "category = '" . $conn->real_escape_string($category_filter) . "'";
}

if (!empty($condition_filter)) {
    $conditions[] = "cond = '" . $conn->real_escape_string($condition_filter) . "'";
}

$where_clause = '';
if (count($conditions) > 0) {
    $where_clause = " WHERE " . implode(' AND ', $conditions);
    $sql .= $where_clause;
}

$count_sql = "SELECT COUNT(*) AS total_items FROM inventory" . $where_clause;
$count_result = $conn->query($count_sql);
$total_items = $count_result->fetch_assoc()['total_items'];
$total_pages = ceil($total_items / $items_per_page);

$sql .= " ORDER BY id DESC LIMIT $items_per_page OFFSET $offset";

$q = $conn->query($sql);

$query_params = http_build_query([
    'search' => $search,
    'category_filter' => $category_filter,
    'condition_filter' => $condition_filter
]);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
        }
        .pagination a, .pagination span {
            text-decoration: none;
            color: #333;
            padding: 10px 15px;
            margin: 0 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: #f8f8f8;
            transition: background-color 0.3s, color 0.3s;
            cursor: pointer;
            font-weight: bold;
        }
        .pagination a:hover {
            background-color: #e0e0e0;
        }
        .pagination span.current {
            background-color: #34495e;
            color: white;
            border-color: #34495e;
            cursor: default;
        }
        .pagination span.disabled {
            color: #ccc;
            background-color: #f8f8f8;
            border-color: #ccc;
            cursor: default;
        }
        .pagination a.nav-button {
            padding: 10px 20px;
        }
        
        /* Stats Cards Styling */
        .stat-cards-container {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            padding: 30px 0;
            background-color: #d8e6dd;
            margin-top: 20px;
            border-radius: 8px;
        }
        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            text-align: center;
            flex: 1;
            transition: transform 0.2s;
            border: 1px solid #eee;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .stat-card p {
            margin: 0 0 5px 0;
            color: #555;
            font-size: 14px;
        }
        .stat-card h2 {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
            color: #34495e;
        }
        .stat-card.attention {
            border-bottom: 5px solid #e74c3c;
        }
        .stat-card.attention h2 {
            color: #e74c3c;
        }
        
        /* NEW: Targeted Condition Styling */
        .condition-badge {
            display: inline-block;
            padding: 5px 10px; /* Padding para sa 'oval' effect */
            border-radius: 15px; /* Para maging oval */
            font-weight: bold;
            text-transform: capitalize;
            text-align: center;
            line-height: 1;
        }
        
        .cond-fail-badge {
            background-color: #fddddd; /* Light red background */
            color: #d9534f; /* Darker red text */
        }
        .cond-good-badge {
            background-color: #d9edf7; /* Light blue background */
            color: #31708f; /* Darker blue text */
        }
        .cond-excellent-badge {
            background-color: #dff0d8; /* Light green background */
            color: #3c763d; /* Darker green text */
        }
    </style>
</head>
<body>
    <?php include 'dashboard_sidebar.php'; ?>
    <div class="content">
        <h1>Inventory</h1>

        <div class="top-row-inventory">
            <form method="get" class="full-control-row-inventory">
                <input type="text" name="search" placeholder="Search anything..."
                        value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit">Search</button>
                
                <select name="category_filter" onchange="this.form.submit()">
                    <option value="" <?php echo $category_filter == '' ? 'selected' : ''; ?>>All Category</option>
                    <option value="sport" <?php echo $category_filter == 'sport' ? 'selected' : ''; ?>>Sport</option>
                    </select>
                
                <select name="condition_filter" onchange="this.form.submit()">
                    <option value="" <?php echo $condition_filter == '' ? 'selected' : ''; ?>>All Condition</option>
                    <option value="excellent" <?php echo $condition_filter == 'excellent' ? 'selected' : ''; ?>>Excellent</option>
                    <option value="good" <?php echo $condition_filter == 'good' ? 'selected' : ''; ?>>Good</option>
                    <option value="fail" <?php echo $condition_filter == 'fail' ? 'selected' : ''; ?>>Fail</option>
                </select>
            </form>
            
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <button onclick="document.getElementById('addForm').style.display='flex'">+ Add Item</button>
            <?php endif; ?>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Condition</th>
                    <th>Location</th>
                    <th>Last update</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $q->fetch_assoc()): 
                    // Determine the CSS class based on the condition
                    $condition_class_badge = '';
                    $cond_lower = strtolower($row['cond']);
                    if ($cond_lower == 'fail') {
                        $condition_class_badge = 'cond-fail-badge';
                    } elseif ($cond_lower == 'good') {
                        $condition_class_badge = 'cond-good-badge';
                    } elseif ($cond_lower == 'excellent') {
                        $condition_class_badge = 'cond-excellent-badge';
                    }
                ?>
                <tr> 
                    <td><?php echo htmlspecialchars($row['item']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo (int)$row['quantity']; ?></td>
                    <td>
                        <span class="condition-badge <?php echo $condition_class_badge; ?>">
                            <?php echo htmlspecialchars($row['cond']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                    <td><?php echo htmlspecialchars($row['last_update']); ?></td>
                    <td>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="inventory.php?edit=<?php echo $row['id']; ?>">Edit</a> |
                        <a href="backend/delete_item.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Delete?')">Delete</a>
                        <?php else: ?>
                        <span style="color: #999;">View Only</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($q->num_rows == 0): ?>
                    <tr><td colspan="7" style="text-align: center;">Walang nakitang item.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            
            <?php if ($current_page > 1): ?>
                <a href="inventory.php?page=<?php echo $current_page - 1; ?>&<?php echo $query_params; ?>" class="nav-button">
                    &lt; Previous
                </a>
            <?php else: ?>
                <span class="nav-button disabled">
                    &lt; Previous
                </span>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $current_page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="inventory.php?page=<?php echo $i; ?>&<?php echo $query_params; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($current_page < $total_pages): ?>
                <a href="inventory.php?page=<?php echo $current_page + 1; ?>&<?php echo $query_params; ?>" class="nav-button">
                    Next &gt;
                </a>
            <?php else: ?>
                <span class="nav-button disabled">
                    Next &gt;
                </span>
            <?php endif; ?>
            
        </div>
        <?php endif; ?>
        <div class="stat-cards-container">
            <div class="stat-card">
                <p>Total Item</p>
                <h2><?php echo $total_item; ?></h2>
            </div>
            <div class="stat-card">
                <p>Total Quantity</p>
                <h2><?php echo $total_quantity; ?></h2>
            </div>
            <div class="stat-card">
                <p>Categories</p>
                <h2><?php echo $total_categories; ?></h2>
            </div>
            <div class="stat-card attention">
                <p>Need Attention</p>
                <h2><?php echo $need_attention; ?></h2>
            </div>
        </div>
        <div id="addForm" class="modal" style="display:none;">
            <form action="backend/add_item.php" method="post" class="modal-content">
                <h3>Add Item</h3>
                <input name="item" placeholder="Item" required>
                <input name="category" placeholder="Category">
                <input name="quantity" type="number" placeholder="Quantity" required>
                <input name="cond" placeholder="Condition (e.g., excellent, good, fail)" value="good">
                <input name="location" placeholder="Location">
                <input name="last_update" type="date" value="<?php echo date('Y-m-d'); ?>">
                
                <div class="modal-buttons">
                    <button type="submit">Save</button>
                    <button type="button" onclick="this.closest('.modal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>

    </div>
<script src="js/app.js"></script>
</body>
</html>