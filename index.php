<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css"
          integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB"
          crossorigin="anonymous">
    <title>Knižnica</title>
</head>

<body>
<?php
$servername = "xkuran-mysql.mysql.database.azure.com:3306";
$username = "xkuran@xkuran-mysql";
$password = "PHP-testovaci";
$dbname = "library";

$conn = new mysqli($servername, $username, $password, $dbname);


$stmt = $conn->prepare("INSERT INTO books (name, isbn, price, category, author )VALUES (?,?,?,?,?)");
$stmt->bind_param("ssdss", $name, $isbn, $price, $category, $author);
$nameErr = $isbnErr = $priceErr = $categoryErr = $authorErr = "";
$nameInv = $isbnInv = $priceInv = $categoryInv = $authorInv = "border border-secondary";
$name = $isbn = $price = $category = $author = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $valid = 1;
    if (empty($_POST["name"])) {
        $nameErr = "Pole s názvom je povinné.";
        $nameInv = "is-invalid";
        $valid = 0;
    } else {
        $name = test_input($_POST["name"]);
    }

    if (empty($_POST["isbn"])) {
        $isbnErr = "Pole s ISBN je povinné.";
        $isbnInv = "is-invalid";
        $valid = 0;
    } else {
        $isbn = test_input($_POST["isbn"]);
    }

    if (empty($_POST["price"])) {
        $priceErr = "Pole s cenou je povinné.";
        $priceInv = "is-invalid";
        $valid = 0;
    } else {
        if (is_numeric(test_input($_POST["price"]))) {
            $price = test_input($_POST["price"]);
        } else {
            $priceInv = "is-invalid";
            $priceErr = "Hodnota musí byť číselná.";
            $valid = 0;
        }
    }

    if (empty($_POST["category"])) {
        $categoryInv = "is-invalid";
        $categoryErr = "Pole s výberom kategórie je povinné.";
        $valid = 0;
    } else {
        $category = test_input($_POST["category"]);
    }

    if (empty($_POST["author"])) {
        $authorInv = "is-invalid";
        $authorErr = "Pole s menom autora je povinné.";
        $valid = 0;
    } else {
        $author = test_input($_POST["author"]);
    }
    if ($valid) {
        $stmt->execute();
    }
}


function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

?>
<h1 class="display-3 text-center my-4">Knižnica</h1>
<div class="container">
    <form class="my-5" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form-row my-4">
            <div class="col">
                <input type="text" class="form-control <?php echo $nameInv; ?>" placeholder="Názov knihy" name="name"
                       value="<?php echo $name; ?>">
                <div class="invalid-feedback"><?php echo $nameErr; ?></div>
            </div>
        </div>
        <div class="form-row my-4">
            <div class="col">
                <input type="text" class="form-control <?php echo $isbnInv; ?>" placeholder="ISBN" name="isbn"
                       value="<?php echo $isbn; ?>">
                <div class="invalid-feedback"><?php echo $isbnErr; ?></div>
            </div>
            <div class="col">
                <input type="text" class="form-control <?php echo $priceInv; ?>" placeholder="Cena" name="price"
                       value="<?php echo $price; ?>">
                <div class="invalid-feedback"><?php echo $priceErr; ?></div>
            </div>
        </div>
        <div class="form-row my-4">
            <div class="col">
                <select class="form-control <?php echo $categoryInv; ?>" name="category">
                    <option value="" disabled selected hidden>Kategória</option>
                    <?php
                    $sql = "SELECT * FROM categories";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            if ($row["name"] == $category) {
                                echo "<option selected value='" . $row["name"] . "'>" . $row["name"] . "</option>";
                            } else {
                                echo "<option value='" . $row["name"] . "'>" . $row["name"] . "</option>";
                            }
                        }
                    } else {
                        echo "0 results";
                    }
                    ?>
                </select>
                <div class="invalid-feedback"><?php echo $categoryErr; ?></div>
            </div>
            <div class="col">
                <input type="text" class="form-control <?php echo $authorInv; ?> " name="author" placeholder="Autor"
                       value="<?php echo $author; ?>">
                <div class="invalid-feedback"><?php echo $authorErr; ?></div>
            </div>
        </div>
        <button class="btn btn-dark border border-secondary float-right" type="submit" name="submit" value="Submit">
            Pridať knihu do knižnice
        </button>
        <br>
    </form>
    <hr>
    <table class="table table-bordered my-5 table-dark" id="books">
        <thead>
        <tr>
            <th>Názov knihy</th>
            <th>ISBN</th>
            <th onclick="sortTable()">Cena</th>
            <th>Kategória</th>
            <th>Autor</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT * FROM books";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["name"] . "</td><td>" . $row["isbn"] . "</td><td>" . $row["price"] . "€</td><td>" . $row["category"] . "</td><td>" . $row["author"] . "</td></tr>";
            }
        } else {
            echo "0 results";
        }
        ?>
        </tbody>
    </table>

</div>
<script>
    function sortTable() {
        var table, rows, switching, i, x, y, shouldSwitch;
        table = document.getElementById("books");
        switching = true;
        while (switching) {
            switching = false;
            rows = table.rows;
            for (i = 1; i < (rows.length - 1); i++) {
                shouldSwitch = false;
                x = rows[i].getElementsByTagName("TD")[2];
                y = rows[i + 1].getElementsByTagName("TD")[2];
                if (Number(x.innerHTML.slice(0, -1)) > Number(y.innerHTML.slice(0, -1))) {
                    shouldSwitch = true;
                    break;
                }
            }
            if (shouldSwitch) {
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
            }
        }
    }
</script>
</body>

</html>
<?php
$conn->close();
?>