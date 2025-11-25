<?php
/**
 * PHP RSA ID Validator Demo
 * 
 * Simple web interface to demonstrate the validator
 * Note: This is for demonstration purposes only
 * Remove or secure this file in production environments
 * 
 * @package     PhpRsaIdValidator
 * @author      Lwando Nkenjana
 * @copyright   2024 NITS Tech Systems
 * @license     MIT
 */

declare(strict_types=1);

// Security header
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

require __DIR__ . '/../vendor/autoload.php';

use PhpRsaIdValidator\RsaIdValidator;

$result = null;
$idNumber = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $idNumber = filter_input(INPUT_POST, 'id_number', FILTER_SANITIZE_STRING);
    $idNumber = preg_replace('/[^0-9]/', '', $idNumber ?? '');
    
    if (!empty($idNumber)) {
        $validator = new RsaIdValidator();
        $result = $validator->validate($idNumber);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP RSA ID Validator Demo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            max-width: 500px;
            width: 100%;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card-header {
            background: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .card-header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .card-header p {
            opacity: 0.8;
            font-size: 14px;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #3498db;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        button:hover {
            background: #2980b9;
        }
        
        .result {
            margin-top: 25px;
            padding: 20px;
            border-radius: 8px;
            display: none;
        }
        
        .result.valid {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            display: block;
        }
        
        .result.invalid {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            display: block;
        }
        
        .result h3 {
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .result-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .result-item {
            padding: 8px 0;
        }
        
        .result-label {
            font-weight: 600;
            font-size: 14px;
        }
        
        .result-value {
            font-size: 14px;
        }
        
        .error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>PHP RSA ID Validator</h1>
                <p>Validate South African ID Numbers</p>
            </div>
            <div class="card-body">
                <form method="post" id="validationForm">
                    <div class="form-group">
                        <label for="id_number">South African ID Number:</label>
                        <input 
                            type="text" 
                            id="id_number" 
                            name="id_number" 
                            placeholder="e.g. 9001014800081" 
                            value="<?= htmlspecialchars($idNumber) ?>" 
                            maxlength="13"
                            required
                        >
                        <div class="error" id="formatError"></div>
                    </div>
                    <button type="submit">Validate ID Number</button>
                </form>

                <?php if ($result !== null): ?>
                    <div class="result <?= $result['valid'] ? 'valid' : 'invalid' ?>">
                        <h3>
                            <?= $result['valid'] ? '✅ Valid ID Number' : '❌ Invalid ID Number' ?>
                        </h3>
                        <?php if ($result['valid']): ?>
                            <div class="result-details">
                                <div class="result-item">
                                    <div class="result-label">ID Number:</div>
                                    <div class="result-value"><?= htmlspecialchars($result['id_number']) ?></div>
                                </div>
                                <div class="result-item">
                                    <div class="result-label">Date of Birth:</div>
                                    <div class="result-value"><?= htmlspecialchars($result['date_of_birth']) ?></div>
                                </div>
                                <div class="result-item">
                                    <div class="result-label">Gender:</div>
                                    <div class="result-value"><?= htmlspecialchars($result['gender']) ?></div>
                                </div>
                                <div class="result-item">
                                    <div class="result-label">Citizenship:</div>
                                    <div class="result-value"><?= htmlspecialchars($result['citizenship']) ?></div>
                                </div>
                                <div class="result-item">
                                    <div class="result-label">Check Digit:</div>
                                    <div class="result-value"><?= htmlspecialchars($result['check_digit']) ?></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p><strong>Error:</strong> <?= htmlspecialchars($result['error']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="footer">
                    <p>PHP RSA ID Validator v1.0.0 | Secure Validation Library</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('validationForm').addEventListener('submit', function(e) {
            const idInput = document.getElementById('id_number');
            const errorDiv = document.getElementById('formatError');
            const idValue = idInput.value.trim();
            
            // Basic client-side validation
            if (!/^\d{13}$/.test(idValue)) {
                e.preventDefault();
                errorDiv.textContent = 'ID must contain exactly 13 digits';
                idInput.focus();
            } else {
                errorDiv.textContent = '';
            }
        });
        
        // Real-time format validation
        document.getElementById('id_number').addEventListener('input', function(e) {
            const errorDiv = document.getElementById('formatError');
            const value = e.target.value.replace(/[^0-9]/g, '');
            
            if (value.length > 13) {
                e.target.value = value.slice(0, 13);
            }
            
            if (value.length === 13) {
                errorDiv.textContent = '';
            } else if (value.length > 0) {
                errorDiv.textContent = `${13 - value.length} digits remaining`;
            } else {
                errorDiv.textContent = '';
            }
        });
    </script>
</body>
</html>