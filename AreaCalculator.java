// AreaCalculator.java - Калькулятор площади фигур на Java (CLI)
import java.io.*;
import java.nio.file.*;
import java.util.*;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;

public class AreaCalculator {
    private static final Scanner scanner = new Scanner(System.in);
    private static final String HISTORY_FILE = "area_history.json";
    private static final double PI = Math.PI;

    static class Shape {
        String name;
        java.util.function.Function<List<Double>, Double> func;
        List<String> params;

        Shape(String name, java.util.function.Function<List<Double>, Double> func, String... params) {
            this.name = name;
            this.func = func;
            this.params = Arrays.asList(params);
        }
    }

    static class HistoryEntry {
        String date;
        String shape;
        String params;
        String result;
    }

    private static final Map<String, Shape> shapes = new LinkedHashMap<>();

    static {
        shapes.put("1", new Shape("Круг", p -> PI * p.get(0) * p.get(0), "радиус"));
        shapes.put("2", new Shape("Прямоугольник", p -> p.get(0) * p.get(1), "длину", "ширину"));
        shapes.put("3", new Shape("Треугольник", p -> 0.5 * p.get(0) * p.get(1), "основание", "высоту"));
        shapes.put("4", new Shape("Квадрат", p -> p.get(0) * p.get(0), "сторону"));
        shapes.put("5", new Shape("Трапеция", p -> (p.get(0) + p.get(1)) / 2 * p.get(2), "основание a", "основание b", "высоту"));
        shapes.put("6", new Shape("Параллелограмм", p -> p.get(0) * p.get(1), "основание", "высоту"));
        shapes.put("7", new Shape("Ромб", p -> p.get(0) * p.get(1) / 2, "диагональ 1", "диагональ 2"));
        shapes.put("8", new Shape("Эллипс", p -> PI * p.get(0) * p.get(1), "полуось a", "полуось b"));
        shapes.put("9", new Shape("Правильный многоугольник", p -> (p.get(0) * p.get(1) * p.get(1)) / (4 * Math.tan(PI / p.get(0))), "количество сторон", "длину стороны"));
        shapes.put("10", new Shape("Сфера (площадь поверхности)", p -> 4 * PI * p.get(0) * p.get(0), "радиус"));
        shapes.put("11", new Shape("Куб (площадь поверхности)", p -> 6 * p.get(0) * p.get(0), "сторону"));
        shapes.put("12", new Shape("Цилиндр (площадь поверхности)", p -> 2 * PI * p.get(0) * (p.get(0) + p.get(1)), "радиус", "высоту"));
        shapes.put("13", new Shape("Конус (площадь поверхности)", p -> PI * p.get(0) * (p.get(0) + p.get(1)), "радиус", "образующую"));
    }

    public static void saveHistory(HistoryEntry entry) {
        List<HistoryEntry> history = loadHistory();
        history.add(entry);
        try (PrintWriter pw = new PrintWriter(HISTORY_FILE)) {
            pw.println("[");
            for (int i = 0; i < history.size(); i++) {
                HistoryEntry e = history.get(i);
                pw.printf("  {\"date\":\"%s\",\"shape\":\"%s\",\"params\":\"%s\",\"result\":\"%s\"}%s\n",
                        e.date, e.shape, e.params, e.result, (i < history.size() - 1 ? "," : ""));
            }
            pw.println("]");
        } catch (IOException ex) {}
    }

    public static List<HistoryEntry> loadHistory() {
        List<HistoryEntry> history = new ArrayList<>();
        try {
            String json = new String(Files.readAllBytes(Paths.get(HISTORY_FILE)));
            // Упрощённый парсинг (в реальном проекте использовать Jackson)
            // В этой версии оставляем заглушку
        } catch (Exception e) {}
        return history;
    }

    public static void exportHistory(String filename) {
        List<HistoryEntry> history = loadHistory();
        if (history.isEmpty()) {
            System.out.println("История пуста.");
            return;
        }
        try (PrintWriter pw = new PrintWriter(filename)) {
            pw.println("=== ИСТОРИЯ ВЫЧИСЛЕНИЙ ===\n");
            for (HistoryEntry e : history) {
                pw.println(e.date);
                pw.println("Фигура: " + e.shape);
                pw.println("Параметры: " + e.params);
                pw.println("Результат: " + e.result);
                pw.println("-".repeat(40));
            }
            System.out.println("История сохранена в " + filename);
        } catch (IOException ex) {
            System.out.println("Ошибка сохранения: " + ex.getMessage());
        }
    }

    public static double getDouble(String prompt) {
        while (true) {
            System.out.print(prompt);
            try {
                double val = Double.parseDouble(scanner.nextLine().trim());
                if (val > 0) return val;
                System.out.println("Введите положительное число.");
            } catch (NumberFormatException e) {
                System.out.println("Введите число.");
            }
        }
    }

    public static void main(String[] args) {
        System.out.println("📐 КАЛЬКУЛЯТОР ПЛОЩАДИ ФИГУР");
        List<HistoryEntry> history = loadHistory();

        while (true) {
            System.out.println("\nВыберите фигуру:");
            for (Map.Entry<String, Shape> entry : shapes.entrySet()) {
                System.out.println(entry.getKey() + ". " + entry.getValue().name);
            }
            System.out.println("h. Показать историю");
            System.out.println("e. Экспорт истории");
            System.out.println("0. Выход");

            String choice = scanner.nextLine().trim();

            if (choice.equals("0")) break;
            else if (choice.equalsIgnoreCase("h")) {
                if (history.isEmpty()) {
                    System.out.println("История пуста.");
                } else {
                    System.out.println("\n=== ИСТОРИЯ ===");
                    int start = Math.max(0, history.size() - 10);
                    for (int i = start; i < history.size(); i++) {
                        HistoryEntry e = history.get(i);
                        System.out.printf("%s | %s | %s\n", e.date.substring(0, 19), e.shape, e.result);
                    }
                }
                continue;
            } else if (choice.equalsIgnoreCase("e")) {
                exportHistory("history.txt");
                continue;
            } else if (shapes.containsKey(choice)) {
                Shape shape = shapes.get(choice);
                System.out.println("\nФигура: " + shape.name);
                List<Double> params = new ArrayList<>();
                for (String pname : shape.params) {
                    params.add(getDouble("Введите " + pname + ": "));
                }
                double result = shape.func.apply(params);
                String resultStr = String.format("%.4f кв. ед.", result);
                System.out.printf("\nПлощадь %s: %s\n", shape.name.toLowerCase(), resultStr);

                System.out.print("Сохранить результат? (y/n): ");
                String save = scanner.nextLine().trim().toLowerCase();
                if (save.equals("y")) {
                    HistoryEntry entry = new HistoryEntry();
                    entry.date = LocalDateTime.now().format(DateTimeFormatter.ISO_LOCAL_DATE_TIME);
                    entry.shape = shape.name;
                    entry.params = params.toString().replace("[", "").replace("]", "");
                    entry.result = resultStr;
                    saveHistory(entry);
                    history.add(entry);
                    System.out.println("✅ Сохранено!");
                }
            } else {
                System.out.println("Неверный выбор.");
            }
        }
    }
}
