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
<h1 class="display-3 text-center my-4">Knižnica</h1>
<div class="container">
    <form class="my-5" id="book_form">
        <div class="form-row my-4">
            <div class="col">
                <input type="text" class="form-control " placeholder="Názov knihy" name="name"
                       id="name">
                <div id="nameError" class="invalid-feedback"></div>
            </div>
        </div>
        <div class="form-row my-4">
            <div class="col">
                <input type="text" class="form-control" placeholder="ISBN" name="isbn"
                       id="isbn">
                <div id="isbnError" class="invalid-feedback"></div>
            </div>
            <div class="col">
                <input type="text" class="form-control " placeholder="Cena" name="price"
                       id="price">
                <div id="priceError" class="invalid-feedback"></div>
            </div>
        </div>
        <div class="form-row my-4">
            <div class="col">
                <select id="categories" class="form-control" name="category">
                    <option value='' disabled selected hidden> Kategória</option>
                </select>
                <div id="categoriesError" class="invalid-feedback"></div>
            </div>
            <div class="col">
                <input type="text" class="form-control " name="author" placeholder="Autor"
                       id="author">
                <ul id="autocomplete" class="list-group" style="position: absolute "></ul>
                <div id="authorError" class="invalid-feedback"></div>
            </div>
        </div>
        <button class="btn btn-dark border border-secondary float-right" type="submit">
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
            <th onclick="sortByPrice()">Cena</th>
            <th>Kategória</th>
            <th>Autor</th>
        </tr>
        </thead>
        <tbody id="book_table">

        </tbody>
    </table>

</div>

<script>
    function bookTable(data) {
        for (let i = 0; i < data.length; i++) {
            var table = document.getElementById("book_table");
            var row = table.insertRow(-1);
            var name = row.insertCell(0);
            var isbn = row.insertCell(1);
            var price = row.insertCell(2);
            var category = row.insertCell(3);
            var author = row.insertCell(4);
            name.innerHTML = data[i].name;
            isbn.innerHTML = data[i].isbn;
            price.innerHTML = data[i].price + "€";
            category.innerHTML = data[i].category;
            author.innerHTML = data[i].author;
        }
    }

    function categoryOptions(data) {
        var select = document.getElementById("categories");
        for (let row in data) {
            var option = document.createElement("option");
            option.value = data[row].name;
            option.text = data[row].name;
            select.add(option);
        }
    }

    function sortByPrice() {
        document.getElementById('book_table').innerHTML = "";
        fetch(window.location.hostname + "/json/?order_by=price").then(response => response.json()).then(data => bookTable(data))
    }

    function errors(data) {
        if (data == "sucess") {
            alert("Kniha bola úspešne pridaná.")
        } else {
            if ("name" in data) {
                document.getElementById("nameError").innerHTML = "Zadaj názov knihy.";
                document.getElementById("name").classList.add("is-invalid");
            } else {
                document.getElementById("nameError").innerHTML = "";
                document.getElementById("name").classList.remove("is-invalid");
            }
            if ("isbn" in data) {
                document.getElementById("isbnError").innerHTML = "Zadaj ISBN knihy.";
                document.getElementById("isbn").classList.add("is-invalid");
            } else {
                document.getElementById("isbnError").innerHTML = "";
                document.getElementById("isbn").classList.remove("is-invalid");
            }
            if ("price" in data) {
                document.getElementById("priceError").innerHTML = "Zadaj cenu knihy.";
                document.getElementById("price").classList.add("is-invalid");
                if (data['price'][0] == "not_number") {
                    document.getElementById("priceError").innerHTML = "Zadaj číslnú hodnotu.";
                }
            } else {
                document.getElementById("priceError").innerHTML = "";
                document.getElementById("price").classList.remove("is-invalid");
            }
            if ("category" in data) {
                document.getElementById("categoriesError").innerHTML = "Zadaj kategóriu knihy.";
                document.getElementById("categories").classList.add("is-invalid");
            } else {
                document.getElementById("categoriesError").innerHTML = "";
                document.getElementById("categories").classList.remove("is-invalid");
            }
            if ("author" in data) {
                document.getElementById("authorError").innerHTML = "Zadaj autora knihy.";
                document.getElementById("author").classList.add("is-invalid");
            } else {
                document.getElementById("authorError").innerHTML = "";
                document.getElementById("author").classList.remove("is-invalid");
            }
        }
    }

    const book_form = document.getElementById('book_form');

    book_form.addEventListener('submit', function (e) {
        e.preventDefault();
        name = document.getElementById("name").value;
        isbn = document.getElementById("isbn").value;
        price = document.getElementById("price").value;
        category = document.getElementById("categories").value;
        author = document.getElementById("author").value;

        const data = {"name": name, "isbn": isbn, "price": price, "category": category, "author": author};
        fetch(window.location.hostname + "/json", {
            method: 'POST',
            headers: {'Content-Type': 'application/json',},
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(data => {
                errors(data);
            })
    });

    fetch(window.location.hostname + "/json/categories").then(response => response.json()).then(data => categoryOptions(data))
    fetch(window.location.hostname + "/json").then(response => response.json()).then(data => bookTable(data))
    fetch(window.location.hostname + "/json/authors").then(response => response.json()).then(data => author_autocomplete(data))

    function complete_author(data) {
        document.getElementById("author").value = data.innerHTML;
        return document.getElementById('autocomplete').innerHTML = "";
    }

    function author_autocomplete(data) {
        var authors = [];
        for (let i = 0; i < data.length; i++) {
            authors.push(data[i].name);
        }
        document.getElementById('author').addEventListener('keyup', e => {
            input = e.target.value;
            if (input.length) {
                results = authors.filter((item) => {
                    return item.toLowerCase().includes(input.toLowerCase());
                });
                renderResults(results);
            } else {
                document.getElementById('autocomplete').innerHTML = "";
            }

        });

        function renderResults(results) {
            if (!results.length) {
                return document.getElementById('autocomplete').innerHTML = "";
            }
            const content = results
                .map((item) => {
                    return `<li class="list-group-item" onclick="complete_author(this)">${item}</li>`;
                })
                .join('');
            document.getElementById('autocomplete').innerHTML = "";
            document.getElementById('autocomplete').innerHTML = content;
        }
    }

</script>

</body>

</html>
