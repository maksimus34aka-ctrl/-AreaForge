// AreaCalculator.cs - Калькулятор площади фигур на C# (CLI)
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text.Json;

namespace AreaCalculator
{
    class Shape
    {
        public string Name { get; set; }
        public Func<double[], double> Func { get; set; }
        public string[] Params { get; set; }
    }

    class HistoryEntry
    {
        public string Date { get; set; }
        public string Shape { get; set; }
        public string Params { get; set; }
        public string Result { get; set; }
    }

    class Program
    {
        private static readonly double PI = Math.PI;
        private static readonly string HISTORY_FILE = "area_history.json";
        private static readonly Dictionary<string, Shape> shapes = new Dictionary<string, Shape>();

        static Program()
        {
            shapes["1"] = new Shape { Name = "Круг", Func = p => PI * p[0] * p[0], Params = new[] { "радиус" } };
            shapes["2"] = new Shape { Name = "Прямоугольник", Func = p => p[0] * p[1], Params = new[] { "длину", "ширину" } };
            shapes["3"] = new Shape { Name = "Треугольник", Func = p => 0.5 * p[0] * p[1], Params = new[] { "основание", "высоту" } };
            shapes["4"] = new Shape { Name = "Квадрат", Func = p => p[0] * p[0], Params = new[] { "сторону" } };
            shapes["5"] = new Shape { Name = "Трапеция", Func = p => (p[0] + p[1]) / 2 * p[2], Params = new[] { "основание a", "основание b", "высоту" } };
            shapes["6"] = new Shape { Name = "Параллелограмм", Func = p => p[0] * p[1], Params = new[] { "основание", "высоту" } };
            shapes["7"] = new Shape { Name = "Ромб", Func = p => p[0] * p[1] / 2, Params = new[] { "диагональ 1", "диагональ 2" } };
            shapes["8"] = new Shape { Name = "Эллипс", Func = p => PI * p[0] * p[1], Params = new[] { "полуось a", "полуось b" } };
            shapes["9"] = new Shape { Name = "Правильный многоугольник", Func = p => (p[0] * p[1] * p[1]) / (4 * Math.Tan(PI / p[0])), Params = new[] { "количество сторон", "длину стороны" } };
            shapes["10"] = new Shape { Name = "Сфера (площадь поверхности)", Func = p => 4 * PI * p[0] * p[0], Params = new[] { "радиус" } };
            shapes["11"] = new Shape { Name = "Куб (площадь поверхности)", Func = p => 6 * p[0] * p[0], Params = new[] { "сторону" } };
            shapes["12"] = new Shape { Name = "Цилиндр (площадь поверхности)", Func = p => 2 * PI * p[0] * (p[0] + p[1]), Params = new[] { "радиус", "высоту" } };
            shapes["13"] = new Shape { Name = "Конус (площадь поверхности)", Func = p => PI * p[0] * (p[0] + p[1]), Params = new[] { "радиус", "образующую" } };
        }

        static void SaveHistory(HistoryEntry entry)
        {
            List<HistoryEntry> history = LoadHistory();
            history.Add(entry);
            string json = JsonSerializer.Serialize(history, new JsonSerializerOptions { WriteIndented = true });
            File.WriteAllText(HISTORY_FILE, json);
        }

        static List<HistoryEntry> LoadHistory()
        {
            if (File.Exists(HISTORY_FILE))
            {
                try
                {
                    string json = File.ReadAllText(HISTORY_FILE);
                    return JsonSerializer.Deserialize<List<HistoryEntry>>(json) ?? new List<HistoryEntry>();
                }
                catch { }
            }
            return new List<HistoryEntry>();
        }

        static void ExportHistory(string filename)
        {
            var history = LoadHistory();
            if (!history.Any())
            {
                Console.WriteLine("История пуста.");
                return;
            }
            using (var sw = new StreamWriter(filename))
            {
                sw.WriteLine("=== ИСТОРИЯ ВЫЧИСЛЕНИЙ ===\n");
                foreach (var e in history)
                {
                    sw.WriteLine(e.Date);
                    sw.WriteLine($"Фигура: {e.Shape}");
                    sw.WriteLine($"Параметры: {e.Params}");
                    sw.WriteLine($"Результат: {e.Result}");
                    sw.WriteLine(new string('-', 40));
                }
            }
            Console.WriteLine($"История сохранена в {filename}");
        }

        static double GetDouble(string prompt)
        {
            while (true)
            {
                Console.Write(prompt);
                string input = Console.ReadLine();
                if (double.TryParse(input, out double val) && val > 0)
                    return val;
                Console.WriteLine("Введите положительное число.");
            }
        }

        static void Main()
        {
            Console.WriteLine("📐 КАЛЬКУЛЯТОР ПЛОЩАДИ ФИГУР");
            var history = LoadHistory();

            while (true)
            {
                Console.WriteLine("\nВыберите фигуру:");
                foreach (var kv in shapes)
                    Console.WriteLine($"{kv.Key}. {kv.Value.Name}");
                Console.WriteLine("h. Показать историю");
                Console.WriteLine("e. Экспорт истории");
                Console.WriteLine("0. Выход");

                string choice = Console.ReadLine();

                if (choice == "0") break;
                else if (choice?.ToLower() == "h")
                {
                    if (!history.Any())
                    {
                        Console.WriteLine("История пуста.");
                    }
                    else
                    {
                        Console.WriteLine("\n=== ИСТОРИЯ ===");
                        foreach (var e in history.Skip(Math.Max(0, history.Count - 10)))
                            Console.WriteLine($"{e.Date.Substring(0, 19)} | {e.Shape} | {e.Result}");
                    }
                    continue;
                }
                else if (choice?.ToLower() == "e")
                {
                    ExportHistory("history.txt");
                    continue;
                }
                else if (shapes.ContainsKey(choice))
                {
                    var shape = shapes[choice];
                    Console.WriteLine($"\nФигура: {shape.Name}");
                    var paramsList = new List<double>();
                    foreach (var pname in shape.Params)
                        paramsList.Add(GetDouble($"Введите {pname}: "));
                    double result = shape.Func(paramsList.ToArray());
                    string resultStr = $"{result:F4} кв. ед.";
                    Console.WriteLine($"\nПлощадь {shape.Name.ToLower()}: {resultStr}");

                    Console.Write("Сохранить результат? (y/n): ");
                    if (Console.ReadLine()?.ToLower() == "y")
                    {
                        var entry = new HistoryEntry
                        {
                            Date = DateTime.Now.ToString("o"),
                            Shape = shape.Name,
                            Params = string.Join(", ", paramsList),
                            Result = resultStr
                        };
                        SaveHistory(entry);
                        history.Add(entry);
                        Console.WriteLine("✅ Сохранено!");
                    }
                }
                else
                {
                    Console.WriteLine("Неверный выбор.");
                }
            }
        }
    }
}
