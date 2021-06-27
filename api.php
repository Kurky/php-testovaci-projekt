<?php

$servername = "xkuran-mysql.mysql.database.azure.com:3306";
$username = "xkuran@xkuran-mysql";
$password = "PHP-testovaci";
$dbname = "library";
$conn = new mysqli($servername, $username, $password, $dbname);


$request = $_SERVER['REQUEST_URI'];

switch ($_SERVER["REQUEST_METHOD"]) {
    case "GET":
        if (preg_match("/\/json\/categories*/", $request)) {
            get_categories();
        } else if (preg_match("/\/json\/authors*/", $request)) {
            get_authors();
        } else {
            get_books();
        }
        break;
    case "POST":
        post();
        break;
    default:
        http_response_code(404);
        echo "404 Not Found";
}

function get_categories()
{
    $sql = "SELECT name from categories;";
    $result = $GLOBALS["conn"]->query($sql);
    show_results($result);
}

function get_authors()
{
    $sql = "SELECT name from authors;";
    $result = $GLOBALS["conn"]->query($sql);
    show_results($result);
}

function get_books()
{

    if (isset($_GET['order_by'])) {
        if ($_GET['order_by'] == "price") {
            $sql = "SELECT b.id, b.name, b.isbn, b.price, c.name as category, a.name as author FROM books b LEFT JOIN categories c ON b.categories_id = c.id LEFT JOIN authors a ON b.authors_id = a.id ORDER BY b.price;";
        } else {
            $sql = "SELECT b.id, b.name, b.isbn, b.price, c.name as category, a.name as author FROM books b LEFT JOIN categories c ON b.categories_id = c.id LEFT JOIN authors a ON b.authors_id = a.id ORDER BY b.id;";
        }
    } else {
        $sql = "SELECT b.id, b.name, b.isbn, b.price, c.name as category, a.name as author FROM books b LEFT JOIN categories c ON b.categories_id = c.id LEFT JOIN authors a ON b.authors_id = a.id ORDER BY b.id;";
    }
    $result = $GLOBALS["conn"]->query($sql);
    show_results($result);
}

function show_results($result)
{
    if ($result->num_rows > 0) {
        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($data);
    } else {
        echo "{}";
        http_response_code(404);
        header('Content-Type: application/json');
    }
}

function post()
{
    $data = json_decode(file_get_contents('php://input'), true);
    $errors = array();
    $stmt = $GLOBALS["conn"]->prepare("INSERT INTO books (name, isbn, price, categories_id, authors_id )VALUES (?, ?, ?, (SELECT id from categories WHERE name = ? ), (SELECT id from authors WHERE name = ? ))");
    $valid = 1;
    $reasons = array();
    if (isset($data['name']) && $data['name'] != "") {
        $name = test_input($data['name']);
    } else {
        $valid = 0;
        array_push($reasons, "required");
    }
    if (count($reasons) != 0) {
        $errors["name"] = $reasons;
    }

    $reasons = array();
    if (isset($data['isbn']) && $data['isbn'] != "") {
        $isbn = test_input($data['isbn']);
    } else {
        $valid = 0;
        array_push($reasons, "required");
    }
    if (count($reasons) != 0) {
        $errors["isbn"] = $reasons;
    }

    $reasons = array();
    if (isset($data['price']) && $data['price'] != "") {
        if (is_numeric($data['price'])) {
            $price = test_input($data['price']);
        } else {
            $valid = 0;
            array_push($reasons, "not_number");
        }
    } else {
        $valid = 0;
        array_push($reasons, "required");
    }
    if (count($reasons) != 0) {
        $errors["price"] = $reasons;
    }

    $reasons = array();
    if (isset($data['category']) && $data['category'] != "") {
        $category = test_input($data['category']);
    } else {
        $valid = 0;
        array_push($reasons, "required");
    }
    if (count($reasons) != 0) {
        $errors["category"] = $reasons;
    }

    $reasons = array();
    if (isset($data['author']) && $data['author'] != "") {
        $author = test_input($data['author']);
    } else {
        $valid = 0;
        array_push($reasons, "required");
    }
    if (count($reasons) != 0) {
        $errors["author"] = $reasons;
    }

    if ($valid) {
        $author_exist = 0;
        $sql = "SELECT name from authors;";
        $result = $GLOBALS["conn"]->query($sql);
        while ($row = $result->fetch_assoc()) {
            if ($row['name'] == $data['author']) {
                $author_exist = 1;
                break;
            }
        }
        if ($author_exist == 0) {
            $author_name = test_input($data['author']);
            $insert_autor = $GLOBALS["conn"]->prepare("INSERT INTO authors (name) VALUES (?)");
            $insert_autor->bind_param("s", $author_name);
            $insert_autor->execute();
        }
        $stmt->bind_param("ssdss", $name, $isbn, $price, $category, $author);
        $stmt->execute();
        http_response_code(201);
        header('Content-Type: application/json');
        echo json_encode("sucess");
    } else {
        header('Content-Type: application/json');
        echo json_encode($errors);
    }
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
