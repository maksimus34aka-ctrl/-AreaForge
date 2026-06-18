#!/usr/bin/env node
/**
 * area_calculator.js - Калькулятор площади фигур на JavaScript (Node.js CLI)
 */
const fs = require('fs');
const readline = require('readline');
const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});

const PI = Math.PI;
const HISTORY_FILE = 'area_history.json';

const shapes = {
    '1': { name: 'Круг', func: (r) => PI * r * r, params: ['радиус'] },
    '2': { name: 'Прямоугольник', func: (l, w) => l * w, params: ['длину', 'ширину'] },
    '3': { name: 'Треугольник', func: (b, h) => 0.5 * b * h, params: ['основание', 'высоту'] },
    '4': { name: 'Квадрат', func: (s) => s * s, params: ['сторону'] },
    '5': { name: 'Трапеция', func: (a, b, h) => (a + b) / 2 * h, params: ['основание a', 'основание b', 'высоту'] },
    '6': { name: 'Параллелограмм', func: (b, h) => b * h, params: ['основание', 'высоту'] },
    '7': { name: 'Ромб', func: (d1, d2) => (d1 * d2) / 2, params: ['диагональ 1', 'диагональ 2'] },
    '8': { name: 'Эллипс', func: (a, b) => PI * a * b, params: ['полуось a', 'полуось b'] },
    '9': { name: 'Правильный многоугольник', func: (n, s) => (n * s * s) / (4 * Math.tan(PI / n)), params: ['количество сторон', 'длину стороны'] },
    '10': { name: 'Сфера (площадь поверхности)', func: (r) => 4 * PI * r * r, params: ['радиус'] },
    '11': { name: 'Куб (площадь поверхности)', func: (s) => 6 * s * s, params: ['сторону'] },
    '12': { name: 'Цилиндр (площадь поверхности)', func: (r, h) => 2 * PI * r * (r + h), params: ['радиус', 'высоту'] },
    '13': { name: 'Конус (площадь поверхности)', func: (r, l) => PI * r * (r + l), params: ['радиус', 'образующую'] },
};

function prompt(query) {
    return new Promise(resolve => rl.question(query, resolve));
}

function saveHistory(entry) {
    let history = [];
    if (fs.existsSync(HISTORY_FILE)) {
        try {
            history = JSON.parse(fs.readFileSync(HISTORY_FILE, 'utf8'));
        } catch {}
    }
    history.push(entry);
    fs.writeFileSync(HISTORY_FILE, JSON.stringify(history, null, 2));
}

function loadHistory() {
    if (fs.existsSync(HISTORY_FILE)) {
        try {
            return JSON.parse(fs.readFileSync(HISTORY_FILE, 'utf8'));
        } catch {}
    }
    return [];
}

function exportHistory(filename = 'history.txt') {
    const history = loadHistory();
    if (!history.length) {
        console.log('История пуста.');
        return;
    }
    const lines = ['=== ИСТОРИЯ ВЫЧИСЛЕНИЙ ===\n'];
    history.forEach(entry => {
        lines.push(`${entry.date}`);
        lines.push(`Фигура: ${entry.shape}`);
        lines.push(`Параметры: ${entry.params}`);
        lines.push(`Результат: ${entry.result}`);
        lines.push('-'.repeat(40));
    });
    fs.writeFileSync(filename, lines.join('\n'), 'utf8');
    console.log(`История сохранена в ${filename}`);
}

async function main() {
    console.log('📐 КАЛЬКУЛЯТОР ПЛОЩАДИ ФИГУР');
    let history = loadHistory();

    while (true) {
        console.log('\nВыберите фигуру:');
        for (const [key, shape] of Object.entries(shapes)) {
            console.log(`${key}. ${shape.name}`);
        }
        console.log('h. Показать историю');
        console.log('e. Экспорт истории');
        console.log('0. Выход');

        const choice = await prompt('Ваш выбор: ');

        if (choice === '0') break;
        else if (choice.toLowerCase() === 'h') {
            if (!history.length) {
                console.log('История пуста.');
            } else {
                console.log('\n=== ИСТОРИЯ ===');
                history.slice(-10).forEach(entry => {
                    console.log(`${entry.date.slice(0,19)} | ${entry.shape} | ${entry.result}`);
                });
            }
            continue;
        } else if (choice.toLowerCase() === 'e') {
            exportHistory();
            continue;
        } else if (shapes[choice]) {
            const shape = shapes[choice];
            const params = [];
            console.log(`\nФигура: ${shape.name}`);
            for (const pname of shape.params) {
                while (true) {
                    const input = await prompt(`Введите ${pname}: `);
                    const val = parseFloat(input);
                    if (!isNaN(val) && val > 0) {
                        params.push(val);
                        break;
                    }
                    console.log('Введите положительное число.');
                }
            }
            const result = shape.func(...params);
            const resultStr = `${result.toFixed(4)} кв. ед.`;
            console.log(`\nПлощадь ${shape.name.toLowerCase()}: ${resultStr}`);

            const save = await prompt('Сохранить результат? (y/n): ');
            if (save.toLowerCase() === 'y') {
                const entry = {
                    date: new Date().toISOString(),
                    shape: shape.name,
                    params: params.join(', '),
                    result: resultStr
                };
                saveHistory(entry);
                history.push(entry);
                console.log('✅ Сохранено!');
            }
        } else {
            console.log('Неверный выбор.');
        }
    }
    rl.close();
}

main().catch(console.error);
