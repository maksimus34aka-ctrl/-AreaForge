// area_calculator.rs - Калькулятор площади фигур на Rust (CLI)
use std::collections::HashMap;
use std::fs;
use std::io::{self, Write};
use std::time::{SystemTime, UNIX_EPOCH};

const PI: f64 = std::f64::consts::PI;
const HISTORY_FILE: &str = "area_history.json";

#[derive(serde::Serialize, serde::Deserialize, Clone)]
struct HistoryEntry {
    date: String,
    shape: String,
    params: String,
    result: String,
}

type ShapeFunc = fn(Vec<f64>) -> f64;

fn get_shapes() -> HashMap<String, (String, ShapeFunc, Vec<String>)> {
    let mut m = HashMap::new();
    m.insert("1".to_string(), ("Круг".to_string(), |p| PI * p[0] * p[0], vec!["радиус".to_string()]));
    m.insert("2".to_string(), ("Прямоугольник".to_string(), |p| p[0] * p[1], vec!["длину".to_string(), "ширину".to_string()]));
    m.insert("3".to_string(), ("Треугольник".to_string(), |p| 0.5 * p[0] * p[1], vec!["основание".to_string(), "высоту".to_string()]));
    m.insert("4".to_string(), ("Квадрат".to_string(), |p| p[0] * p[0], vec!["сторону".to_string()]));
    m.insert("5".to_string(), ("Трапеция".to_string(), |p| (p[0] + p[1]) / 2.0 * p[2], vec!["основание a".to_string(), "основание b".to_string(), "высоту".to_string()]));
    m.insert("6".to_string(), ("Параллелограмм".to_string(), |p| p[0] * p[1], vec!["основание".to_string(), "высоту".to_string()]));
    m.insert("7".to_string(), ("Ромб".to_string(), |p| p[0] * p[1] / 2.0, vec!["диагональ 1".to_string(), "диагональ 2".to_string()]));
    m.insert("8".to_string(), ("Эллипс".to_string(), |p| PI * p[0] * p[1], vec!["полуось a".to_string(), "полуось b".to_string()]));
    m.insert("9".to_string(), ("Правильный многоугольник".to_string(), |p| (p[0] * p[1] * p[1]) / (4.0 * (PI / p[0]).tan()), vec!["количество сторон".to_string(), "длину стороны".to_string()]));
    m.insert("10".to_string(), ("Сфера (площадь поверхности)".to_string(), |p| 4.0 * PI * p[0] * p[0], vec!["радиус".to_string()]));
    m.insert("11".to_string(), ("Куб (площадь поверхности)".to_string(), |p| 6.0 * p[0] * p[0], vec!["сторону".to_string()]));
    m.insert("12".to_string(), ("Цилиндр (площадь поверхности)".to_string(), |p| 2.0 * PI * p[0] * (p[0] + p[1]), vec!["радиус".to_string(), "высоту".to_string()]));
    m.insert("13".to_string(), ("Конус (площадь поверхности)".to_string(), |p| PI * p[0] * (p[0] + p[1]), vec!["радиус".to_string(), "образующую".to_string()]));
    m
}

fn save_history(entry: &HistoryEntry) {
    let mut history: Vec<HistoryEntry> = Vec::new();
    if let Ok(data) = fs::read_to_string(HISTORY_FILE) {
        if let Ok(parsed) = serde_json::from_str(&data) {
            history = parsed;
        }
    }
    history.push(entry.clone());
    let json = serde_json::to_string_pretty(&history).unwrap();
    fs::write(HISTORY_FILE, json).unwrap();
}

fn load_history() -> Vec<HistoryEntry> {
    if let Ok(data) = fs::read_to_string(HISTORY_FILE) {
        if let Ok(parsed) = serde_json::from_str(&data) {
            return parsed;
        }
    }
    Vec::new()
}

fn export_history(filename: &str) {
    let history = load_history();
    if history.is_empty() {
        println!("История пуста.");
        return;
    }
    let mut content = String::from("=== ИСТОРИЯ ВЫЧИСЛЕНИЙ ===\n\n");
    for entry in &history {
        content.push_str(&format!("{}\n", entry.date));
        content.push_str(&format!("Фигура: {}\n", entry.shape));
        content.push_str(&format!("Параметры: {}\n", entry.params));
        content.push_str(&format!("Результат: {}\n", entry.result));
        content.push_str(&format!("{}\n", "-".repeat(40)));
    }
    fs::write(filename, content).unwrap();
    println!("История сохранена в {}", filename);
}

fn read_line(prompt: &str) -> String {
    print!("{}", prompt);
    io::stdout().flush().unwrap();
    let mut input = String::new();
    io::stdin().read_line(&mut input).unwrap();
    input.trim().to_string()
}

fn get_float(prompt: &str) -> f64 {
    loop {
        let input = read_line(prompt);
        if let Ok(val) = input.parse::<f64>() {
            if val > 0.0 {
                return val;
            }
        }
        println!("Введите положительное число.");
    }
}

fn main() {
    println!("📐 КАЛЬКУЛЯТОР ПЛОЩАДИ ФИГУР");
    let shapes = get_shapes();
    let mut history = load_history();

    loop {
        println!("\nВыберите фигуру:");
        for (key, (name, _, _)) in &shapes {
            println!("{}. {}", key, name);
        }
        println!("h. Показать историю");
        println!("e. Экспорт истории");
        println!("0. Выход");

        let choice = read_line("Ваш выбор: ");

        if choice == "0" {
            break;
        } else if choice == "h" {
            if history.is_empty() {
                println!("История пуста.");
            } else {
                println!("\n=== ИСТОРИЯ ===");
                let start = if history.len() > 10 { history.len() - 10 } else { 0 };
                for entry in &history[start..] {
                    println!("{} | {} | {}", &entry.date[..19], entry.shape, entry.result);
                }
            }
            continue;
        } else if choice == "e" {
            export_history("history.txt");
            continue;
        } else if let Some((name, func, param_names)) = shapes.get(&choice) {
            println!("\nФигура: {}", name);
            let mut params = Vec::new();
            for pname in param_names {
                params.push(get_float(&format!("Введите {}: ", pname)));
            }
            let result = func(params.clone());
            let result_str = format!("{:.4} кв. ед.", result);
            println!("\nПлощадь {}: {}", name.to_lowercase(), result_str);

            let save = read_line("Сохранить результат? (y/n): ");
            if save.to_lowercase() == "y" {
                let entry = HistoryEntry {
                    date: format!("{}", chrono::Local::now().to_rfc3339()),
                    shape: name.clone(),
                    params: params.iter().map(|p| p.to_string()).collect::<Vec<_>>().join(", "),
                    result: result_str.clone(),
                };
                save_history(&entry);
                history.push(entry);
                println!("✅ Сохранено!");
            }
        } else {
            println!("Неверный выбор.");
        }
    }
}
