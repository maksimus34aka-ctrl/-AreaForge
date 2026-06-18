<?php
// area_calculator.php - Калькулятор площади фигур на PHP (CLI + веб)
// CLI: php area_calculator.php

const PI = M_PI;
const HISTORY_FILE = 'area_history.json';

$shapes = [
    '1' => ['name' => 'Круг', 'func' => function($p) { return PI * $p[0] * $p[0]; }, 'params' => ['радиус']],
    '2' => ['name' => 'Прямоугольник', 'func' => function($p) { return $p[0] * $p[1]; }, 'params' => ['длину', 'ширину']],
    '3' => ['name' => 'Треугольник', 'func' => function($p) { return 0.5 * $p[0] * $p[1]; }, 'params' => ['основание', 'высоту']],
    '4' => ['name' => 'Квадрат', 'func' => function($p) { return $p[0] * $p[0]; }, 'params' => ['сторону']],
    '5' => ['name' => 'Трапеция', 'func' => function($p) { return ($p[0] + $p[1]) / 2 * $p[2]; }, 'params' => ['основание a', 'основание b', 'высоту']],
    '6' => ['name' => 'Параллелограмм', 'func' => function($p) { return $p[0] * $p[1]; }, 'params' => ['основание', 'высоту']],
    '7' => ['name' => 'Ромб', 'func' => function($p) { return $p[0] * $p[1] / 2; }, 'params' => ['диагональ 1', 'диагональ 2']],
    '8' => ['name' => 'Эллипс', 'func' => function($p) { return PI * $p[0] * $p[1]; }, 'params' => ['полуось a', 'полуось b']],
    '9' => ['name' => 'Правильный многоугольник', 'func' => function($p) { return ($p[0] * $p[1] * $p[1]) / (4 * tan(PI / $p[0])); }, 'params' => ['количество сторон', 'длину стороны']],
    '10' => ['name' => 'Сфера (площадь поверхности)', 'func' => function($p) { return 4 * PI * $p[0] * $p[0]; }, 'params' => ['радиус']],
    '11' => ['name' => 'Куб (площадь поверхности)', 'func' => function($p) { return 6 * $p[0] * $p[0]; }, 'params' => ['сторону']],
    '12' => ['name' => 'Цилиндр (площадь поверхности)', 'func' => function($p) { return 2 * PI * $p[0] * ($p[0] + $p[1]); }, 'params' => ['радиус', 'высоту']],
    '13' => ['name' => 'Конус (площадь поверхности)', 'func' => function($p) { return PI * $p[0] * ($p[0] + $p[1]); }, 'params' => ['радиус', 'образующую']],
];

function saveHistory($entry) {
    $history = [];
    if (file_exists(HISTORY_FILE)) {
        $json = file_get_contents(HISTORY_FILE);
        $history = json_decode($json, true) ?: [];
    }
    $history[] = $entry;
    file_put_contents(HISTORY_FILE, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function loadHistory() {
    if (file_exists(HISTORY_FILE)) {
        $json = file_get_contents(HISTORY_FILE);
        return json_decode($json, true) ?: [];
    }
    return [];
}

function exportHistory($filename) {
    $history = loadHistory();
    if (empty($history)) {
        echo "История пуста.\n";
        return;
    }
    $content = "=== ИСТОРИЯ ВЫЧИСЛЕНИЙ ===\n\n";
    foreach ($history as $entry) {
        $content .= $entry['date'] . "\n";
        $content .= "Фигура: " . $entry['shape'] . "\n";
        $content .= "Параметры: " . $entry['params'] . "\n";
        $content .= "Результат: " . $entry['result'] . "\n";
        $content .= str_repeat("-", 40) . "\n";
    }
    file_put_contents($filename, $content);
    echo "История сохранена в $filename\n";
}

function getFloat($prompt) {
    while (true) {
        echo $prompt;
        $input = trim(fgets(STDIN));
        if (is_numeric($input) && $input > 0) {
            return (float)$input;
        }
        echo "Введите положительное число.\n";
    }
}

if (php_sapi_name() === 'cli') {
    // CLI режим
    echo "📐 КАЛЬКУЛЯТОР ПЛОЩАДИ ФИГУР\n";
    $history = loadHistory();
    global $shapes;

    while (true) {
        echo "\nВыберите фигуру:\n";
        foreach ($shapes as $key => $shape) {
            echo "$key. " . $shape['name'] . "\n";
        }
        echo "h. Показать историю\n";
        echo "e. Экспорт истории\n";
        echo "0. Выход\n";
        echo "Ваш выбор: ";
        $choice = trim(fgets(STDIN));

        if ($choice == '0') break;
        elseif ($choice == 'h') {
            if (empty($history)) {
                echo "История пуста.\n";
            } else {
                echo "\n=== ИСТОРИЯ ===\n";
                $recent = array_slice($history, -10);
                foreach ($recent as $entry) {
                    echo substr($entry['date'], 0, 19) . " | " . $entry['shape'] . " | " . $entry['result'] . "\n";
                }
            }
            continue;
        } elseif ($choice == 'e') {
            exportHistory('history.txt');
            continue;
        } elseif (isset($shapes[$choice])) {
            $shape = $shapes[$choice];
            echo "\nФигура: " . $shape['name'] . "\n";
            $params = [];
            foreach ($shape['params'] as $pname) {
                $params[] = getFloat("Введите $pname: ");
            }
            $result = $shape['func']($params);
            $resultStr = number_format($result, 4) . " кв. ед.";
            echo "\nПлощадь " . strtolower($shape['name']) . ": $resultStr\n";

            echo "Сохранить результат? (y/n): ";
            $save = trim(fgets(STDIN));
            if (strtolower($save) == 'y') {
                $entry = [
                    'date' => date('c'),
                    'shape' => $shape['name'],
                    'params' => implode(', ', $params),
                    'result' => $resultStr
                ];
                saveHistory($entry);
                $history[] = $entry;
                echo "✅ Сохранено!\n";
            }
        } else {
            echo "Неверный выбор.\n";
        }
    }
    exit;
}

// Веб-интерфейс
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📐 Калькулятор площади фигур (PHP)</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7fb; margin: 20px; }
        .container { max-width: 700px; margin: 0 auto; background: white; padding: 20px; border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: inline-block; width: 140px; }
        input, select, button { padding: 6px; border-radius: 4px; border: 1px solid #ccc; }
        button { background: #3498db; color: white; border: none; cursor: pointer; padding: 6px 20px; }
        button:hover { background: #2980b9; }
        .result { background: #ecf0f1; padding: 15px; border-radius: 8px; margin-top: 20px; }
        .history { margin-top: 20px; background: #f8f9fa; padding: 10px; border-radius: 8px; max-height: 200px; overflow-y: auto; }
    </style>
</head>
<body>
<div class="container">
    <h1>📐 Калькулятор площади фигур (PHP)</h1>
    <form method="GET">
        <div class="form-group">
            <label>Выберите фигуру:</label>
            <select name="shape">
                <?php foreach ($shapes as $key => $shape): ?>
                    <option value="<?= $key ?>" <?= isset($_GET['shape']) && $_GET['shape'] == $key ? 'selected' : '' ?>>
                        <?= $shape['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="params-container">
            <?php if (isset($_GET['shape']) && isset($shapes[$_GET['shape']])): 
                $selected = $shapes[$_GET['shape']];
                foreach ($selected['params'] as $idx => $pname): ?>
                    <div class="form-group">
                        <label>Введите <?= $pname ?>:</label>
                        <input type="number" step="any" name="param_<?= $idx ?>" value="<?= $_GET["param_$idx"] ?? '' ?>" required>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="submit">Рассчитать</button>
        <a href="?export=1">📥 Экспорт истории</a>
    </form>

    <?php if (isset($_GET['shape']) && isset($_GET['param_0'])): 
        $shape = $shapes[$_GET['shape']];
        $params = [];
        for ($i = 0; $i < count($shape['params']); $i++) {
            if (!isset($_GET["param_$i"]) || $_GET["param_$i"] === '') break;
            $val = (float)$_GET["param_$i"];
            if ($val <= 0) { echo "<div class='result' style='background:#fadbd8;'>Ошибка: все параметры должны быть положительными.</div>"; break 2; }
            $params[] = $val;
        }
        if (count($params) == count($shape['params'])) {
            $result = $shape['func']($params);
            $resultStr = number_format($result, 4) . " кв. ед.";
            echo "<div class='result'><strong>Результат:</strong> Площадь " . strtolower($shape['name']) . " = $resultStr</div>";
            // Сохранение в историю
            $entry = [
                'date' => date('c'),
                'shape' => $shape['name'],
                'params' => implode(', ', $params),
                'result' => $resultStr
            ];
            saveHistory($entry);
        }
    endif; ?>

    <?php if (isset($_GET['export'])): 
        exportHistory('history.txt');
        echo "<div class='result'>✅ История сохранена в history.txt</div>";
    endif; ?>

    <div class="history">
        <h3>📊 Последние вычисления</h3>
        <?php $history = loadHistory(); ?>
        <?php if (empty($history)): ?>
            <p>История пуста.</p>
        <?php else: ?>
            <?php foreach (array_slice($history, -5) as $entry): ?>
                <div style="border-bottom:1px solid #eee; padding:5px 0;">
                    <strong><?= $entry['shape'] ?></strong> = <?= $entry['result'] ?>
                    <span style="color:#999; font-size:12px;"><?= substr($entry['date'], 0, 16) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
