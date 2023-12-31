<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Интернет магазин чая</title>
</head>

<body bgcolor="wheat">

    <?php

    // Переменные
    if (!isset($_SESSION['reg_check']))
        $_SESSION['reg_check'] = false;
    if (!isset($_SESSION['role']))
        $_SESSION['role'] = 'none';
        $error_password = 0;

    // Регистреция классов
    spl_autoload_register();

    //Получаем файлы    
    $users_json = file_get_contents('users.json');
    $arr_users = json_decode($users_json, 1);

    $tea_json = file_get_contents('tea.json');
    $arr_tea = json_decode($tea_json, 1);

    // Авторизация
    
    // Проверка авторизации
    if (isset($_GET['login']) and isset($_GET['password'])) {
        $error_password = 1;
        foreach ($arr_users as $user) {
            if ($_GET['login'] == $user['login']) {
                if ($_GET['password'] == $user['password']) {
                    
                    $error_password = 0; 
    
                    $_SESSION['reg_check'] = true;
                    $_SESSION['login'] = $user['login'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['name'] = $user['name'];
                }
            }
        }
    }

    // Гет запрос для выхода
    if (!empty($_GET['session'])) {
        $_SESSION['reg_check'] = false;
        $_SESSION['role'] = 'none';
        $_GET['session'] = '';
    }

    // Форма
    if ($_SESSION['reg_check'] == false) {
        ?>
        <div class="form_reg">
            <form action="" method="GET">
                <b>Авторизация</b> <br>
                Введите логин: <br>
                <input required name="login"></input> <br>
                Введите пароль: <br>
                <input type="password" required name="password"></input> <br>

                <?php
                if ($error_password) {
                    echo '<div class = "link2">Неправильный логин или пароль</div>';
                }
                ?>

                <input value="Войти" type="submit"></input>
                <div class="butten">
                    <a class="link" href="register.php">Зарегестрироваться</a>
                </div>
        </div>
        </form>
        <?php
    }

    // Тело сайта
    echo '<div class = "main">';

    // Гет запрос для admin
    if (isset($_GET['admin'])) {
        unset($arr_tea[$_GET['admin']]);
        $arr_tea = array_values($arr_tea);
    }

    // Гет запрос для user
    if (isset($_GET['user'])) {
        foreach ($arr_users as $key => $user) {
            if ($user['login'] == $_SESSION['login']) {
                $user_key = $key;
            }
        }

        if (in_array($_GET['user'], $arr_users[$user_key]['favourites']) == false) {
            $arr_users[$user_key]['favourites'][] = $_GET['user'];

            $users_json = json_encode($arr_users);
            file_put_contents('users.json', $users_json);
        }
    }

    // Создание объектов массива
    $ObjectTea = [];
    foreach ($arr_tea as $tea) {
        $ObjectTea[] = new Tea($tea['name'], $tea['description'], $tea['category'], $tea['price'], $tea['imageUrl'], $tea['stock'], $tea['offer'], $tea['id']);
    }

    if ($_SESSION['reg_check'] == true) {

        // Приветствие
        if ($_SESSION['reg_check'] == true) {
            echo "<div class = 'h1'><h1>Привет " . $_SESSION['name'] . "!</h1></div>";
        }

        // Проверка формы
        if (!empty($_POST)) {
            if (!empty($_POST['imageUrl'])) {
                $imageUrl = $_POST['imageUrl'];
            } else
                $imageUrl = '';

            if (!empty($_POST['offer'])) {
                $offer = $_POST['offer'];
            } else
                $offer = false;

            if (!empty($_POST['stock'])) {
                $stock = $_POST['stock'];
            } else
                $stock = '';

            if (!empty($_POST['category_dop'])) {
                $category = $_POST['category_dop'];
            } else
                $category = $_POST['category'];

            $id = $arr_tea[array_key_last($arr_tea)]['id'] + 1;

            $ObjectTea[] = new Tea($_POST['name'], $_POST['description'], $category, $_POST['price'], $imageUrl, $_POST['stock'], $offer, $id);

            $arr_tea[] = ['name' => $_POST['name'], 'description' => $_POST['description'], 'category' => $category, 'price' => $_POST['price'], 'imageUrl' => $imageUrl, 'stock' => $stock, 'offer' => $offer, 'id' => $id,];
        }
    }

    // Форма
    if ($_SESSION['role'] == 'admin') {
        ?>
        <div class="form">
            <form action="" method="POST">
                <b>Форма добавления нового чая</b> <br>
                Введите название чая: <br>
                <input required name="name"></input> <br>
                Введите описание товара: <br>
                <textarea required name="description"></textarea> <br>
                Введите его категорию: <br>
                <select name="category">
                    <?php
                    foreach ($categories as $category) {
                        echo '<option value="' . $category . '">' . $category . '</option>';
                    }
                    ?>
                </select> <br>
                Поле ввода для новой категории: <br>
                <input name="category_dop"></input> <br>
                Введите стоимость товара: <br>
                <input required type="number" name="price"></input> <br>
                Введите URL картинки (необязательно): <br>
                <input name="imageUrl"></input> <br>
                Введите количество товара: <br>
                <input required type="number" name="stock"></input> <br>
                Введите скидку (необязательно): <br>
                <input name="offer"></input> <br>
                <input type="submit"></input>
            </form>
        </div>
        <?php
    }

    // Создание массива с категориями
    $categories = [];
    foreach ($arr_tea as $tea) {
        if (!array_search($tea['category'], $categories)) {
            $categories[] = $tea['category'];
        }
    }

    // Ввывод объектов
    foreach ($categories as $category) {
        echo '<div class="category">';
        echo '<div class = "title">' . $category . '</div>';
        foreach ($ObjectTea as $key => $Object) {
            if ($category == $Object->getCategory()) {
                echo '<div class="tea">';
                $Object->print();

                // Проверка на роль
                if ($_SESSION['role'] == 'admin') {
                    ?>
                    <div class="butten">
                        <a class="link" href="?admin=<?= $key ?>">Удалить товар</a> <br>
                    </div>
                    <?php
                }

                if ($_SESSION['role'] == 'user') {
                    ?>
                    <div class="butten">
                        <a class="link" href="?user=<?= $Object->getId() ?>">Добавить в избранное</a> <br>
                    </div>
                    <?php
                }

                echo '</div>';
            }
        }
        echo '</div>';
    }

    // Перезапись файла
    $tea_json = json_encode($arr_tea);
    file_put_contents('tea.json', $tea_json);

    echo '</div>';
    if ($_SESSION['reg_check'] == true) {
        ?>

        <!-- Выход из сессии -->
        <div class="session">
            <a class="link" href="?session='1'">Выход из акаунта</a>
        </div>

        <?php
    }

    if ($_SESSION['role'] == 'user') {
        ?>

        <!-- Избранное -->
        <div class="main_out">
            <a class="link" href="favourites.php">Избранное</a>
        </div>

        <?php
    }

    ?>

    <!-- Стили -->
    <style>
        *,
        ::after,
        ::before {
            box-sizing: border-box;
        }

        body {
            background-image: url(Фон.jpg);
            background-size: 20%;
        }

        p {
            margin: 4px 0px 0px 0px;
        }

        .h1 {
            width: 350px;
            background-color: white;
            text-align: center;
            color: #9932CC;
            margin: 3% auto 3%;
            border: 2px solid black;
            border-radius: 10px;
        }

        <?php if ($_SESSION['role'] == 'admin') {
            echo ".category {
            width: 650px;
            height: auto;
            font-size: 40px;
            color: white;
            background-color: brown;
            text-align: center;
            padding: 10px;
            margin: 0px auto 30px auto;
            border: 2px solid black;
            border-radius: 20px;
        }

        .tea {                    
            width: 600px;
            height: auto;
            font-size: 28px;
            text-align: center;
            color: black;
            background-color: white;
            padding: 10px;
            margin: auto;
            margin-top: 10px;
            margin-bottom: 18px;
            border: 2px solid black;
            border-radius: 20px;
        }
        
        .butten {
            width: 200px;
        }";
        } else {
            echo ".category {
            float: left;
            width: 550px;
            height: auto;
            font-size: 40px;
            color: white;
            background-color: brown;
            text-align: center;
            padding: 10px;
            margin: 0 0.5em 30px 1em;
            border: 2px solid black;
            border-radius: 20px;
        }

        .tea { 
            width: 500px;
            height: auto;
            font-size: 28px;
            text-align: center;
            color: black;
            background-color: white;
            padding: 10px;
            margin: auto;
            margin-top: 10px;
            margin-bottom: 18px;
            border: 2px solid black;
            border-radius: 20px;
        }

        .body {
            justify-content: space-between;
        }
        
        .butten {
            width: 300px;
        }";
        }
        ?>
        .form {
            position: fixed;
            top: 30px;
            left: 2%;
            width: 530px;
            height: auto;
            font-size: 28px;
            text-align: center;
            background-color: white;
            padding: 20px 0 20px 0;
            margin: auto;
            border: 2px solid black;
            border-radius: 20px;
        }

        .form_reg {
            width: 530px;
            height: auto;
            font-size: 28px;
            text-align: center;
            background-color: white;
            padding: 20px 0 20px 0;
            margin: auto;
            margin-top: 3%;
            margin-bottom: 3%;
            border: 2px solid black;
            border-radius: 20px;
        }

        .butten {
            height: auto;
            background-color: #4169E1;
            margin: 10px auto 0px auto;
            padding: 2px;
            border-radius: 10px;
            border: 1px solid black;
        }

        .link {
            font-size: 28px;
            color: white;
            text-decoration: none;
        }

        .link2 {
            font-size: 28px;
            color: #DC143C;
            text-decoration: none;
            margin: auto;
        }

        .center {
            width: 300px;
            text-align: center;
            margin: auto;
        }

        .session {
            position: fixed;
            top: 56em;
            text-align: center;
            right: 5px;
            width: 230px;
            height: auto;
            background-color: #4169E1;
            margin: 10px auto 0px auto;
            padding: 2px;
            border-radius: 10px;
            border: 1px solid black;
        }

        .main_out {
            position: fixed;
            top: 56em;
            text-align: center;
            left: 5px;
            width: 160px;
            height: auto;
            background-color: #4169E1;
            margin: 10px auto 0px auto;
            padding: 2px;
            border-radius: 10px;
            border: 1px solid black;
        }

        input,
        select,
        textarea {
            width: 350px;
            height: 35px;
            max-width: 450px;
            max-height: 90px;
            font-size: 21px;
            margin: 12px 0 12px 0;
        }
    </style>

</body>

</html>