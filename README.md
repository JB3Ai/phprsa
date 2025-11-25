
````md

# **PHP RSA ID Validator**
````
![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)
![License](https://img.shields.io/badge/License-MIT-green.svg)
![Packagist](https://img.shields.io/badge/Packagist-Ready-orange.svg)

A professional, lightweight PHP library for validating South African ID numbers.
It performs full structural checks, validates birth dates, determines gender, identifies citizenship, and verifies the Luhn check digit. Ideal for forms, authentication systems, HR platforms, and any application requiring reliable South African ID verification.


---

## Features

- Validates full RSA ID format (YYMMDDSSSGC A)
- Birth date extraction with automatic century detection
- Gender identification based on sequence number
- Citizen vs resident status
- Full Luhn algorithm check digit validation
- JSON API support

---

## Installation

### Using Composer

```bash
composer require phprsa/id-validator
````

### Manual Load

If you’re not using Composer, include the file directly:

```php
require 'vendor/phprsa/id-validator/src/RsaIdValidator.php';
```

---

## Usage Example

```php
use PhpRsaIdValidator\RsaIdValidator;

$validator = new RsaIdValidator();
$result = $validator->validate('9001014800085');

if ($result['valid']) {
    echo "ID is valid";
} else {
    echo "Error: " . $result['error'];
}
```

---

## API Endpoint

This project includes a working API endpoint for remote validation.

### `id-api.php`

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use PhpRsaIdValidator\RsaIdValidator;

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id   = $data['id'] ?? null;

if (!$id) {
    echo json_encode([
        'success' => false,
        'error'   => 'No ID number supplied'
    ]);
    exit;
}

$validator = new RsaIdValidator();
echo json_encode($validator->validate($id), JSON_PRETTY_PRINT);
```

### Example Request

```
POST /id-api.php
Content-Type: application/json
```

```json
{
    "id": "9001014800085"
}
```

### Example Response

```json
{
    "valid": true,
    "id_number": "9001014800085",
    "date_of_birth": "1990-01-01",
    "gender": "Male",
    "citizenship": "SA Citizen",
    "check_digit": 5
}
```

---

## Browser Test Page (`id.php`)

A small interface for manual validation:

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use PhpRsaIdValidator\RsaIdValidator;

$id       = $_GET['id'] ?? null;
$validator = new RsaIdValidator();
$result    = $id ? $validator->validate($id) : null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>RSA ID Validator</title>
    <style>
        body { font-family: Arial; margin: 40px; }
        input { padding: 8px; width: 260px; }
        button { padding: 8px 18px; }
        .box { margin-top: 20px; padding: 15px; border-radius: 6px; }
        .ok { background: #d4edda; }
        .bad { background: #f8d7da; }
    </style>
</head>
<body>

<h2>RSA ID Validator</h2>

<form method="GET">
    <input type="text" name="id" placeholder="Enter ID" value="<?= htmlspecialchars($id) ?>">
    <button type="submit">Check</button>
</form>

<?php if ($result): ?>
<div class="box <?= $result['valid'] ? 'ok' : 'bad' ?>">
    <?php if ($result['valid']): ?>
        <strong>Valid</strong><br><br>
        ID: <?= $result['id_number'] ?><br>
        DOB: <?= $result['date_of_birth'] ?><br>
        Gender: <?= $result['gender'] ?><br>
        Citizenship: <?= $result['citizenship'] ?><br>
        Check Digit: <?= $result['check_digit'] ?><br>
    <?php else: ?>
        <strong>Invalid</strong><br><br>
        <?= $result['error'] ?>
    <?php endif; ?>
</div>
<?php endif; ?>

</body>
</html>
```

---

## Test Client (`test-api.php`)

```php
<?php

$url = "https://yourdomain.com/id-api.php";

$payload = json_encode(["id" => "9001014800085"]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

$response = curl_exec($ch);
curl_close($ch);

echo "<pre>";
print_r(json_decode($response, true));
echo "</pre>";
```

---

## .gitignore

```
vendor/
composer.lock
.env
*.log
.DS_Store
.idea/
```

---

## Author

NITS TECH SYSTEMS
[https://www.nitstechsystems.co.za](https://www.nitstechsystems.co.za)

For support or integration assistance: [lwandonkenjana@gmail.com](mailto:lwandonkenjana@gmail.com)

```

---

