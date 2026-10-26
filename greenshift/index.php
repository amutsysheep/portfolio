<?php
session_start();

require_once 'connection/connection.php';
require_once 'inc/user/cookies.inc.php';

/*
========================================================
I. BASE URL CONFIGURATION (HTTP only, query-string routing)
========================================================
*/
$protocol = 'http'; // always http
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';

/**
 * Your app is inside /greenshift. If you ever move it, update this path.
 * Optionally, you could derive it dynamically:
 *   $base_path = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/greenshift'), '/');
 *   $base_url  = $protocol . '://' . $host . $base_path;
 */
$base_url = $protocol . '://' . $host . '/greenshift';

/*
========================================================
II. DEFAULT ROLE SETUP
========================================================
*/
if (!isset($_SESSION['rolename'])) {
    $_SESSION['rolename'] = "logout_user";
}

/*
========================================================
III. PAGE ACCESS FUNCTION
========================================================
*/
function generateUrl($page, $params = [], $panel = null) {
    global $base_url;
    $page = preg_replace('/[^a-z0-9\-]/i', '', $page);
    $query = array_merge(['page' => $page], $params);
    if ($panel && in_array($panel, ['Admin','Driving_Instructor'], true)) {
        $query['panel'] = $panel;
    }
    return $base_url . '/index.php?' . http_build_query($query);
}


/*
========================================================
IV. PAGE ROUTING (query-string only, no / paths)
========================================================
*/
$page     = $_GET['page'] ?? 'home';
$panel    = $_GET['panel'] ?? null;
$is_panel = in_array($panel, ['Admin', 'Driving_Instructor'], true);

/* sanitize */
$page = preg_replace('/[^a-z0-9\-]/i', '', $page);

/*
========================================================
V. AUTHORIZATION & FILE CHECK
========================================================
  Removed 'collections' everywhere since you don't have that page.
*/
$auth = [
        'Admin' => ['home', 'instructormangement', 'lessonpackage', 'studentoverview', 'addinstructor', 'editinstructor', 'editstudent',],
        'Driving_Instructor' => ['home', 'editinstructor', 'addavailability', 'editslot'],
        'Client' => [
                'otp', 'trial-lesson', 'home', 'help',
                'checkout', 'thanks', 'forget-password', 'reset-password', 'about-us',
                'pricing', 'single-lesson', 'single-lesson-info-form', 'single-lesson-succes','basket','driving-lesson-package','ordersuccess', 'pakket-inplannen', 'pakket-inplannen-succes'
        ],
        'logout_user' => [
                'login', 'register', 'otp', 'trial-lesson', 'home', 'help',
                'checkout', 'thanks', 'forgot-password', 'reset-password', 'about-us',
                'pricing', 'trial-lesson-info-form', 'trial-lesson-succes','basket','driving-lesson-package'
        ]
];

$current_role = $_SESSION['rolename'];

/* default to 404; we switch to the correct include if authorized + file exists */
$page_to_include = 'inc/error/404.inc.php';

if (isset($auth[$current_role]) && in_array($page, $auth[$current_role], true)) {
    $admin_candidate               = 'inc/admin/' . $page . '.inc.php';
    $driving_instructor_candidate  = 'inc/driving_instructor/' . $page . '.inc.php';
    $inc_candidate                 = 'inc/user/' . $page . '.inc.php';

    if ($is_panel && $current_role === 'Admin' && file_exists($admin_candidate)) {
        $page_to_include = $admin_candidate;

    } elseif ($is_panel && $current_role === 'Driving_Instructor' && file_exists($driving_instructor_candidate)) {
        $page_to_include = $driving_instructor_candidate;

    } elseif (!$is_panel && file_exists($inc_candidate)) {
        $page_to_include = $inc_candidate;

    } else {
        error_log("Missing include for role [$current_role] / panel [$panel]: $admin_candidate / $driving_instructor_candidate / $inc_candidate");
    }
}

/*
========================================================
VI. PAGE TITLE LOGIC (GreenShift branding)
========================================================
  Also corrected 'about-us' key here.
*/
$pageTitle = "GreenShift";

switch ($page) {
    case 'detail':
        if (isset($_GET['ean'])) {
            try {
                $stmt = $pdo->prepare("SELECT title FROM product WHERE ean = ?");
                $stmt->execute([$_GET['ean']]);
                if ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $pageTitle = htmlspecialchars($product['title']) . " - GreenShift";
                }
            } catch (PDOException $e) {
                // silently ignore
            }
        }
        break;
    case 'about-us': $pageTitle = "About us - GreenShift"; break;
    case 'help': $pageTitle = "Help & Support - GreenShift"; break;
    case 'login': $pageTitle = "Login - GreenShift"; break;
    case 'home': $pageTitle = "Home - GreenShift"; break;
    // add other pages here if you want specific titles
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google" content="notranslate">
    <!-- Adjust CSS paths if needed; both are present to support different setups -->
    <link href="../src/output.css" rel="stylesheet">
    <link href="src/output.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="/img/greenshift.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
</head>
<body class="bg-gray-100">

<?php
// Navbar (hide on terms-of-service)
if ($page !== 'terms-of-service') {
    if (in_array($current_role, ['Admin', 'Driving_Instructor']) && file_exists('inc/navbar/admin-colleague-navbar.inc.php')) {
        include 'inc/navbar/admin-colleague-navbar.inc.php';
    } else {
        include 'inc/navbar/client-navbar.inc.php';
    }
}
?>

<main class="container mx-auto px-4 py-8">
    <?php
    if (!file_exists($page_to_include)) {
        echo "<div class='text-red-500 font-bold'>Error: Could not load page: <code>" . htmlspecialchars($page_to_include) . "</code></div>";
    } else {
        include $page_to_include;
    }
    ?>
</main>

<?php
// Footer for non-admin/colleague and not on terms-of-service
if ($page !== 'terms-of-service' && !in_array($current_role, ['Admin', 'Driving_Instructor'])) {
    include 'inc/footer/footer.inc.php';
}

// Initialize cookie consent if missing
if (!isset($_COOKIE['cookie_consent'])) {
    CookieManager::init();
}
?>

</body>
</html>
