<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BookCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Standard realistic categories
        $categoriesData = [
            ['name' => 'Fiction', 'description' => 'Literary fiction, novels, and narrative stories.'],
            ['name' => 'Non-Fiction', 'description' => 'Informative, educational, and real-life books.'],
            ['name' => 'Science & Technology', 'description' => 'Books covering physics, biology, and scientific breakthroughs.'],
            ['name' => 'Computer Science', 'description' => 'Programming, algorithms, AI, and systems architecture.'],
            ['name' => 'History', 'description' => 'World history, civilizations, and historical events.'],
            ['name' => 'Philosophy', 'description' => 'Classic and modern philosophical thought.'],
            ['name' => 'Business & Economics', 'description' => 'Finance, leadership, economics, and business management.'],
            ['name' => 'Self-Help', 'description' => 'Personal development, habits, and productivity.'],
        ];

        $categories = collect();
        foreach ($categoriesData as $data) {
            $categories->push(
                Category::firstOrCreate(
                    ['name' => $data['name']],
                    ['description' => $data['description']]
                )
            );
        }

        // 2. Realistic sample books
        $sampleBooks = [
            [
                'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'author' => 'Robert C. Martin',
                'isbn' => '978-0132350884',
                'category_name' => 'Computer Science',
                'quantity' => 5,
                'available_quantity' => 5,
                'price' => 45.00,
            ],
            [
                'title' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
                'author' => 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides',
                'isbn' => '978-0201633610',
                'category_name' => 'Computer Science',
                'quantity' => 3,
                'available_quantity' => 3,
                'price' => 54.99,
            ],
            [
                'title' => 'The Pragmatic Programmer: Your Journey to Mastery',
                'author' => 'David Thomas, Andrew Hunt',
                'isbn' => '978-0135957059',
                'category_name' => 'Computer Science',
                'quantity' => 4,
                'available_quantity' => 4,
                'price' => 48.50,
            ],
            [
                'title' => 'Sapiens: A Brief History of Humankind',
                'author' => 'Yuval Noah Harari',
                'isbn' => '978-0062316097',
                'category_name' => 'History',
                'quantity' => 8,
                'available_quantity' => 8,
                'price' => 24.99,
            ],
            [
                'title' => 'To Kill a Mockingbird',
                'author' => 'Harper Lee',
                'isbn' => '978-0060935467',
                'category_name' => 'Fiction',
                'quantity' => 6,
                'available_quantity' => 6,
                'price' => 18.00,
            ],
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'isbn' => '978-0451524935',
                'category_name' => 'Fiction',
                'quantity' => 7,
                'available_quantity' => 7,
                'price' => 15.99,
            ],
            [
                'title' => 'Atomic Habits: An Easy & Proven Way to Build Good Habits & Break Bad Ones',
                'author' => 'James Clear',
                'isbn' => '978-0735211292',
                'category_name' => 'Self-Help',
                'quantity' => 10,
                'available_quantity' => 10,
                'price' => 21.00,
            ],
            [
                'title' => 'A Brief History of Time',
                'author' => 'Stephen Hawking',
                'isbn' => '978-0553380163',
                'category_name' => 'Science & Technology',
                'quantity' => 5,
                'available_quantity' => 5,
                'price' => 19.50,
            ],
            [
                'title' => 'Meditations',
                'author' => 'Marcus Aurelius',
                'isbn' => '978-0140449334',
                'category_name' => 'Philosophy',
                'quantity' => 4,
                'available_quantity' => 4,
                'price' => 12.99,
            ],
            [
                'title' => 'Thinking, Fast and Slow',
                'author' => 'Daniel Kahneman',
                'isbn' => '978-0374533557',
                'category_name' => 'Business & Economics',
                'quantity' => 5,
                'available_quantity' => 5,
                'price' => 22.00,
            ],
        ];

        foreach ($sampleBooks as $bookData) {
            $category = $categories->firstWhere('name', $bookData['category_name']);

            Book::firstOrCreate(
                ['isbn' => $bookData['isbn']],
                [
                    'title' => $bookData['title'],
                    'author' => $bookData['author'],
                    'category_id' => $category?->id,
                    'category' => $bookData['category_name'],
                    'quantity' => $bookData['quantity'],
                    'available_quantity' => $bookData['available_quantity'],
                    'price' => $bookData['price'],
                ]
            );
        }
    }
}

