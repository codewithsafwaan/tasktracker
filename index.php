<?php
session_start();

// Initialize todos array in session if not exists
if (!isset($_SESSION['todos'])) {
    $_SESSION['todos'] = [];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (!empty($_POST['todo'])) {
                    $_SESSION['todos'][] = [
                        'id' => uniqid(),
                        'text' => htmlspecialchars($_POST['todo']),
                        'completed' => false,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                }
                break;

            case 'toggle':
                if (isset($_POST['id'])) {
                    foreach ($_SESSION['todos'] as &$todo) {
                        if ($todo['id'] === $_POST['id']) {
                            $todo['completed'] = !$todo['completed'];
                            break;
                        }
                    }
                }
                break;

            case 'delete':
                if (isset($_POST['id'])) {
                    $_SESSION['todos'] = array_filter($_SESSION['todos'], function($todo) {
                        return $todo['id'] !== $_POST['id'];
                    });
                }
                break;
        }
    }

    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$todos = $_SESSION['todos'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✨ Todo Masterpiece - Organize with Style</title>
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
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .content {
            padding: 40px 30px;
        }

        .add-form {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
            align-items: stretch;
        }

        .add-form input[type="text"] {
            flex: 1;
            padding: 18px 20px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .add-form input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }

        .add-btn {
            padding: 18px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .add-btn:active {
            transform: translateY(0);
        }

        .todo-list {
            list-style: none;
            padding: 0;
        }

        .todo-item {
            display: flex;
            align-items: center;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .todo-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
        }

        .todo-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .todo-item:hover::before {
            width: 8px;
        }

        .todo-item.completed {
            opacity: 0.7;
            background: #f8f9fa;
        }

        .todo-item.completed::before {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .todo-item.completed .todo-text {
            text-decoration: line-through;
            color: #6c757d;
        }

        .todo-checkbox {
            width: 24px;
            height: 24px;
            border: 2px solid #dee2e6;
            border-radius: 50%;
            margin-right: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .todo-item.completed .todo-checkbox {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-color: #28a745;
            color: white;
        }

        .todo-checkbox:hover {
            border-color: #667eea;
            transform: scale(1.1);
        }

        .todo-text {
            flex: 1;
            font-size: 16px;
            line-height: 1.5;
            color: #2d3748;
        }

        .todo-meta {
            font-size: 12px;
            color: #a0aec0;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .todo-actions {
            display: flex;
            gap: 10px;
            margin-left: 20px;
        }

        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-toggle {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }

        .btn-toggle:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }

        .btn-delete {
            background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
        }

        .btn-delete:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #a0aec0;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-text {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .empty-subtext {
            font-size: 0.9rem;
            opacity: 0.7;
        }

        .stats {
            margin-top: 40px;
            padding: 25px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            display: flex;
            justify-content: space-around;
            text-align: center;
        }

        .stat-item {
            flex: 1;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 5px;
        }

        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 15px;
            }

            .header {
                padding: 30px 20px;
            }

            h1 {
                font-size: 2rem;
            }

            .content {
                padding: 30px 20px;
            }

            .add-form {
                flex-direction: column;
                gap: 15px;
            }

            .todo-item {
                padding: 15px;
            }

            .todo-actions {
                flex-direction: column;
                gap: 8px;
            }

            .stats {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✨ Todo Masterpiece</h1>
            <div class="subtitle">Organize your life with style</div>
        </div>

        <div class="content">
            <form method="POST" class="add-form">
                <input type="hidden" name="action" value="add">
                <input type="text" name="todo" placeholder="What needs to be done today?" required>
                <button type="submit" class="add-btn">✨ Add Magic</button>
            </form>

            <?php if (empty($todos)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🌟</div>
                    <div class="empty-text">Your canvas awaits</div>
                    <div class="empty-subtext">Add your first todo above to begin your productive journey</div>
                </div>
            <?php else: ?>
                <ul class="todo-list">
                    <?php foreach ($todos as $todo): ?>
                        <li class="todo-item <?php echo $todo['completed'] ? 'completed' : ''; ?>">
                            <div class="todo-checkbox">
                                <?php if ($todo['completed']): ?>
                                    ✓
                                <?php endif; ?>
                            </div>

                            <div class="todo-text">
                                <?php echo $todo['text']; ?>
                                <div class="todo-meta">
                                    🕒 <?php echo date('M j, Y \a\t g:i A', strtotime($todo['created_at'])); ?>
                                </div>
                            </div>

                            <div class="todo-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?php echo $todo['id']; ?>">
                                    <button type="submit" class="btn btn-toggle">
                                        <?php echo $todo['completed'] ? '↶ Undo' : '✓ Done'; ?>
                                    </button>
                                </form>

                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $todo['id']; ?>">
                                    <button type="submit" class="btn btn-delete" onclick="return confirm('Remove this todo forever?')">
                                        🗑️ Remove
                                    </button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($todos)): ?>
                <div class="stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($todos); ?></span>
                        <div class="stat-label">Total Tasks</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count(array_filter($todos, function($t) { return $t['completed']; })); ?></span>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count(array_filter($todos, function($t) { return !$t['completed']; })); ?></span>
                        <div class="stat-label">Remaining</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
