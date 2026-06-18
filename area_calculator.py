#!/usr/bin/env python3
"""
area_calculator.py - Калькулятор площади фигур на Python (CLI)
Поддерживает: 2D и 3D фигуры, историю, экспорт.
"""
import math
import json
import os
from datetime import datetime

HISTORY_FILE = "area_history.json"
PI = math.pi

def circle_area(radius):
    return PI * radius ** 2

def rectangle_area(length, width):
    return length * width

def triangle_area(base, height):
    return 0.5 * base * height

def square_area(side):
    return side ** 2

def trapezoid_area(a, b, h):
    return (a + b) / 2 * h

def parallelogram_area(base, height):
    return base * height

def rhombus_area(d1, d2):
    return (d1 * d2) / 2

def ellipse_area(a, b):
    return PI * a * b

def regular_polygon_area(n, side):
    return (n * side ** 2) / (4 * math.tan(PI / n))

def sphere_surface_area(radius):
    return 4 * PI * radius ** 2

def cube_surface_area(side):
    return 6 * side ** 2

def cylinder_surface_area(radius, height):
    return 2 * PI * radius * (radius + height)

def cone_surface_area(radius, slant_height):
    return PI * radius * (radius + slant_height)

def get_shapes():
    return {
        '1': ('Круг', circle_area, ['радиус']),
        '2': ('Прямоугольник', rectangle_area, ['длину', 'ширину']),
        '3': ('Треугольник', triangle_area, ['основание', 'высоту']),
        '4': ('Квадрат', square_area, ['сторону']),
        '5': ('Трапеция', trapezoid_area, ['основание a', 'основание b', 'высоту']),
        '6': ('Параллелограмм', parallelogram_area, ['основание', 'высоту']),
        '7': ('Ромб', rhombus_area, ['диагональ 1', 'диагональ 2']),
        '8': ('Эллипс', ellipse_area, ['полуось a', 'полуось b']),
        '9': ('Правильный многоугольник', regular_polygon_area, ['количество сторон', 'длину стороны']),
        '10': ('Сфера (площадь поверхности)', sphere_surface_area, ['радиус']),
        '11': ('Куб (площадь поверхности)', cube_surface_area, ['сторону']),
        '12': ('Цилиндр (площадь поверхности)', cylinder_surface_area, ['радиус', 'высоту']),
        '13': ('Конус (площадь поверхности)', cone_surface_area, ['радиус', 'образующую']),
    }

def save_history(entry):
    history = []
    if os.path.exists(HISTORY_FILE):
        try:
            with open(HISTORY_FILE, 'r', encoding='utf-8') as f:
                history = json.load(f)
        except:
            pass
    history.append(entry)
    with open(HISTORY_FILE, 'w', encoding='utf-8') as f:
        json.dump(history, f, indent=2, ensure_ascii=False)

def load_history():
    if os.path.exists(HISTORY_FILE):
        try:
            with open(HISTORY_FILE, 'r', encoding='utf-8') as f:
                return json.load(f)
        except:
            return []
    return []

def export_history(filename="history.txt"):
    history = load_history()
    if not history:
        print("История пуста.")
        return
    with open(filename, 'w', encoding='utf-8') as f:
        f.write("=== ИСТОРИЯ ВЫЧИСЛЕНИЙ ===\n\n")
        for entry in history:
            f.write(f"{entry['date']}\n")
            f.write(f"Фигура: {entry['shape']}\n")
            f.write(f"Параметры: {entry['params']}\n")
            f.write(f"Результат: {entry['result']}\n")
            f.write("-" * 40 + "\n")
    print(f"История сохранена в {filename}")

def main():
    print("📐 КАЛЬКУЛЯТОР ПЛОЩАДИ ФИГУР")
    shapes = get_shapes()
    history = load_history()

    while True:
        print("\nВыберите фигуру:")
        for key, (name, _, _) in shapes.items():
            print(f"{key}. {name}")
        print("h. Показать историю")
        print("e. Экспорт истории")
        print("0. Выход")

        choice = input("Ваш выбор: ").strip()

        if choice == '0':
            break
        elif choice.lower() == 'h':
            if not history:
                print("История пуста.")
            else:
                print("\n=== ИСТОРИЯ ===")
                for entry in history[-10:]:
                    print(f"{entry['date'][:19]} | {entry['shape']} | {entry['result']}")
            continue
        elif choice.lower() == 'e':
            export_history()
            continue
        elif choice in shapes:
            name, func, param_names = shapes[choice]
            params = []
            print(f"\nФигура: {name}")
            for pname in param_names:
                while True:
                    try:
                        val = float(input(f"Введите {pname}: "))
                        if val <= 0:
                            print("Значение должно быть положительным.")
                            continue
                        params.append(val)
                        break
                    except ValueError:
                        print("Введите число.")
            result = func(*params)
            result_str = f"{result:.4f} кв. ед."
            print(f"\nПлощадь {name.lower()}: {result_str}")

            save = input("Сохранить результат? (y/n): ").strip().lower()
            if save == 'y':
                entry = {
                    'date': datetime.now().isoformat(),
                    'shape': name,
                    'params': ', '.join(str(p) for p in params),
                    'result': result_str
                }
                save_history(entry)
                history.append(entry)
                print("✅ Сохранено!")
        else:
            print("Неверный выбор.")

if __name__ == "__main__":
    main()
