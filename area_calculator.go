// area_calculator.go - Калькулятор площади фигур на Go (CLI)
package main

import (
	"bufio"
	"encoding/json"
	"fmt"
	"math"
	"os"
	"strconv"
	"strings"
	"time"
)

const PI = math.Pi
const HISTORY_FILE = "area_history.json"

type Shape struct {
	Name   string
	Func   func([]float64) float64
	Params []string
}

type HistoryEntry struct {
	Date   string `json:"date"`
	Shape  string `json:"shape"`
	Params string `json:"params"`
	Result string `json:"result"`
}

var shapes = map[string]Shape{
	"1":  {"Круг", func(p []float64) float64 { return PI * p[0] * p[0] }, []string{"радиус"}},
	"2":  {"Прямоугольник", func(p []float64) float64 { return p[0] * p[1] }, []string{"длину", "ширину"}},
	"3":  {"Треугольник", func(p []float64) float64 { return 0.5 * p[0] * p[1] }, []string{"основание", "высоту"}},
	"4":  {"Квадрат", func(p []float64) float64 { return p[0] * p[0] }, []string{"сторону"}},
	"5":  {"Трапеция", func(p []float64) float64 { return (p[0] + p[1]) / 2 * p[2] }, []string{"основание a", "основание b", "высоту"}},
	"6":  {"Параллелограмм", func(p []float64) float64 { return p[0] * p[1] }, []string{"основание", "высоту"}},
	"7":  {"Ромб", func(p []float64) float64 { return p[0] * p[1] / 2 }, []string{"диагональ 1", "диагональ 2"}},
	"8":  {"Эллипс", func(p []float64) float64 { return PI * p[0] * p[1] }, []string{"полуось a", "полуось b"}},
	"9":  {"Правильный многоугольник", func(p []float64) float64 { return (p[0] * p[1] * p[1]) / (4 * math.Tan(PI/p[0])) }, []string{"количество сторон", "длину стороны"}},
	"10": {"Сфера (площадь поверхности)", func(p []float64) float64 { return 4 * PI * p[0] * p[0] }, []string{"радиус"}},
	"11": {"Куб (площадь поверхности)", func(p []float64) float64 { return 6 * p[0] * p[0] }, []string{"сторону"}},
	"12": {"Цилиндр (площадь поверхности)", func(p []float64) float64 { return 2 * PI * p[0] * (p[0] + p[1]) }, []string{"радиус", "высоту"}},
	"13": {"Конус (площадь поверхности)", func(p []float64) float64 { return PI * p[0] * (p[0] + p[1]) }, []string{"радиус", "образующую"}},
}

func saveHistory(entry HistoryEntry) {
	var history []HistoryEntry
	file, err := os.ReadFile(HISTORY_FILE)
	if err == nil {
		json.Unmarshal(file, &history)
	}
	history = append(history, entry)
	data, _ := json.MarshalIndent(history, "", "  ")
	os.WriteFile(HISTORY_FILE, data, 0644)
}

func loadHistory() []HistoryEntry {
	var history []HistoryEntry
	file, err := os.ReadFile(HISTORY_FILE)
	if err != nil {
		return history
	}
	json.Unmarshal(file, &history)
	return history
}

func exportHistory(filename string) {
	history := loadHistory()
	if len(history) == 0 {
		fmt.Println("История пуста.")
		return
	}
	f, _ := os.Create(filename)
	defer f.Close()
	f.WriteString("=== ИСТОРИЯ ВЫЧИСЛЕНИЙ ===\n\n")
	for _, entry := range history {
		f.WriteString(entry.Date + "\n")
		f.WriteString("Фигура: " + entry.Shape + "\n")
		f.WriteString("Параметры: " + entry.Params + "\n")
		f.WriteString("Результат: " + entry.Result + "\n")
		f.WriteString(strings.Repeat("-", 40) + "\n")
	}
	fmt.Printf("История сохранена в %s\n", filename)
}

func getFloat(prompt string, reader *bufio.Reader) float64 {
	for {
		fmt.Print(prompt)
		input, _ := reader.ReadString('\n')
		input = strings.TrimSpace(input)
		val, err := strconv.ParseFloat(input, 64)
		if err == nil && val > 0 {
			return val
		}
		fmt.Println("Введите положительное число.")
	}
}

func main() {
	reader := bufio.NewReader(os.Stdin)
	fmt.Println("📐 КАЛЬКУЛЯТОР ПЛОЩАДИ ФИГУР")
	history := loadHistory()

	for {
		fmt.Println("\nВыберите фигуру:")
		for key, shape := range shapes {
			fmt.Printf("%s. %s\n", key, shape.Name)
		}
		fmt.Println("h. Показать историю")
		fmt.Println("e. Экспорт истории")
		fmt.Println("0. Выход")

		fmt.Print("Ваш выбор: ")
		choice, _ := reader.ReadString('\n')
		choice = strings.TrimSpace(choice)

		if choice == "0" {
			break
		} else if choice == "h" || choice == "H" {
			if len(history) == 0 {
				fmt.Println("История пуста.")
			} else {
				fmt.Println("\n=== ИСТОРИЯ ===")
				start := len(history) - 10
				if start < 0 {
					start = 0
				}
				for i := start; i < len(history); i++ {
					entry := history[i]
					fmt.Printf("%s | %s | %s\n", entry.Date[:19], entry.Shape, entry.Result)
				}
			}
			continue
		} else if choice == "e" || choice == "E" {
			exportHistory("history.txt")
			continue
		} else if shape, ok := shapes[choice]; ok {
			fmt.Printf("\nФигура: %s\n", shape.Name)
			params := make([]float64, len(shape.Params))
			for i, pname := range shape.Params {
				params[i] = getFloat("Введите "+pname+": ", reader)
			}
			result := shape.Func(params)
			resultStr := fmt.Sprintf("%.4f кв. ед.", result)
			fmt.Printf("\nПлощадь %s: %s\n", strings.ToLower(shape.Name), resultStr)

			fmt.Print("Сохранить результат? (y/n): ")
			saveChoice, _ := reader.ReadString('\n')
			saveChoice = strings.TrimSpace(strings.ToLower(saveChoice))
			if saveChoice == "y" {
				entry := HistoryEntry{
					Date:   time.Now().Format(time.RFC3339),
					Shape:  shape.Name,
					Params: strings.Join(strings.Fields(fmt.Sprint(params)), ", "),
					Result: resultStr,
				}
				saveHistory(entry)
				history = append(history, entry)
				fmt.Println("✅ Сохранено!")
			}
		} else {
			fmt.Println("Неверный выбор.")
		}
	}
}
