<?php

namespace Database\Seeders;

use App\Models\BusinessTrainingType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BusinessTrainingSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            require database_path('data/business-training/food-beverage.php'),
        ];

        foreach ($data as $typeData) {
            $type = BusinessTrainingType::create([
                'name' => $typeData['name'],
                'slug' => Str::slug($typeData['name']),
                'icon' => $typeData['icon'],
            ]);

            foreach ($typeData['categories'] as $categoryData) {
                $category = $type->categories()->create([
                    'name' => $categoryData['name'],
                    'slug' => Str::slug($categoryData['name']),
                    'description' => $categoryData['description'],
                ]);

                foreach ($categoryData['modules'] as $moduleIndex => $content) {
                    $category->trainings()->create([
                        'module' => $moduleIndex + 1,
                        'content' => $content,
                    ]);
                }
            }
        }
    }

    /**
     * Organized content structure based on your specific requirements
     */
    private function getFoodCartModules(): array
    {
        return [
            // Module 1
            [
                [
                    'title' => 'What is a Food Cart Business', 
                    'description' => 'A food cart business is a small-scale mobile or stationary food business that sells quick meals or snacks such as siomai, fishballs, burgers, or drinks. It is ideal for entrepreneurs who want to start with low capital, operate in high foot traffic areas, and directly serve customers.'
                ],
                [
                    'title' => 'Advantages & Challenges',
                    'advantages' =>  [
                        'Low startup capital compared to a full restaurant.',
                        'Easy to operate and flexible location options.',
                        'Quick return on investment if products are in demand.',
                        'High customer interaction for building loyalty'
                    ],
                    'challenges' => [
                        'Requires long hours of work and active presence.',
                        'Sales may fluctuate depending on location and season.',
                        'Limited space for equipment and storage.',
                        'Competition from nearby food vendors.'
                    ]
                ],
                [
                    'title' => 'Required mindset',
                    'description' => 'To succeed in a food cart business, you need',
                    'required_mindset' => [
                        [
                            'name' => 'Discipline', 
                            'description' => 'Consistent operation and inventory management.'
                        ],
                        [
                            'name' => 'Customer-focused', 
                            'description' => 'Friendly and reliable service builds trust.'
                        ],
                        [
                            'name' => 'Adaptability', 
                            'description' => 'Adjust menu or strategy according to customer demand.'
                        ],
                        [
                            'name' => 'Resilience', 
                            'description' => 'Handle challenges like slow sales, competition, or weather.'
                        ]
                    ]
                ]
            ],
            // Module 2
            [
                [
                    'title' => 'Choosing your product (fishball, siomai, burgers, etc.)', 
                    'description' => 'Selecting the right product is one of the most important decisions in your food cart business. Choose items that are affordable, easy to prepare, and popular in your target area. Consider factors such as ingredient availability, preparation time, and profit margin. It’s also helpful to focus on a specialty product to stand out from competitors while ensuring consistent quality and taste.'
                ],
                [
                    'title' => 'Identifying your target market', 
                    'description' => 'Your target market refers to the specific group of customers you want to serve. This may include students, office workers, commuters, or local residents. Understanding their preferences, budget, and buying behavior helps you decide what products to sell, how to price them, and how to promote your business effectively.'
                ],
                [
                    'title' => 'Location strategy (high foot traffic areas)', 
                    'description' => 'Choosing the right location is key to increasing sales. Set up your food cart in areas with high foot traffic such as near schools, offices, terminals, markets, or busy streets. Make sure the location is accessible, visible, and safe. A good location ensures a steady flow of customers and maximizes your daily earnings.'
                ]
            ],
            // Module 3
            [
                'title' => 'Sample Budget Breakdown (Philippines)',
                'budget' => [
                    [
                        'item' => 'Food Cart / Stall', 
                        'min_cost' => 8000, 
                        'max_cost' => 20000],
                    [
                        'item' => 'Initial Ingredients', 
                        'min_cost' => 3000, 
                        'max_cost' => 5000
                    ],
                    [
                        'item' => 'Cooking Equipment', 
                        'min_cost' => 3000, 
                        'max_cost' => 7000
                    ],
                    [
                        'item' => 'Permits & Licenses', 
                        'min_cost' => 1000, 
                        'max_cost' => 3000
                    ],
                    [
                        'item' => 'Miscellaneous', 
                        'min_cost' => 2000, 
                        'max_cost' => 5000
                    ]
                ],
                'estimated_total' => ['min_cost' => 17000, 'max_cost' => 37000]
            ],
            // Module 4
            [
                [
                    'title' => 'Setting up your cart', 
                    'description' => 'Setting up your food cart properly ensures smooth operations and attracts customers. Arrange your equipment, ingredients, and tools in an organized and accessible way. Make sure your cart is clean, visually appealing, and branded if possible. Proper setup also includes securing necessary permits and ensuring food safety standards are followed.'
                ],
                [
                    'title' => 'Daily operations checklist', 
                    'description' => 'A daily checklist helps maintain consistency and efficiency in your business. Before opening, prepare ingredients, check supplies, and ensure cleanliness. During operations, focus on fast service, quality food, and customer satisfaction. After closing, clean your cart, record sales, and restock for the next day. Consistent routines help avoid mistakes and improve performance.'
                ],
                [
                    'title' => 'Pricing strategy', 
                    'description' => 'Setting the right price is essential to balance profit and customer demand. Your pricing should cover costs (ingredients, materials, and expenses) while remaining affordable to your target market. Study competitor pricing and consider offering value meals or bundles to attract more customers. The goal is to stay competitive while maintaining good profit margins.'
                ],
                [
                    'title' => 'Inventory management', 
                    'description' => 'Proper inventory management ensures you always have enough supplies without overstocking. Monitor your daily usage of ingredients and materials, and restock regularly based on demand. Avoid waste by tracking expiration dates and maintaining proper storage. Good inventory control helps reduce costs and ensures uninterrupted operations.'
                ]
            ],
            // Module 5
            [
                [
                    'title' => 'Basic marketing (posters, signage)', 
                    'description' => 'Basic marketing helps attract customers even without a big budget. Use eye-catching posters, banners, and clear signage to showcase your products and prices. Make sure your cart is visually appealing, clean, and easy to recognize. Good visibility and simple branding can quickly grab attention and bring in more customers.'
                ],
                [
                    'title' => 'Social media promotion', 
                    'description' => 'Social media is a powerful and low-cost way to promote your food cart. Create a page on platforms like Facebook or Instagram where you can post your menu, location, and daily updates. Share photos of your products, customer feedback, and special offers to engage your audience. Consistent posting helps build awareness and attract more buyers.'
                ],
                [
                    'title' => 'Promo strategies (combo meals, discounts)', 
                    'description' => 'Promos encourage customers to buy more and come back. Offer combo meals (e.g., food + drink), discounts, or “buy 1 take 1” deals to increase sales. You can also create limited-time offers to create urgency. Simple and attractive promotions help boost daily income and build customer loyalty.'
                ]
            ],
            // Module 6
            [
                [
                    'title' => 'Scaling your business', 
                    'description' => 'Scaling your business means increasing your income by improving operations and reaching more customers. This can be done by enhancing your workflow, increasing daily sales, and maintaining consistent quality. Focus on what works best in your current setup and find ways to make it more efficient and profitable.'
                ],
                [
                    'title' => 'Adding more products', 
                    'description' => 'Once your main product is stable and profitable, you can expand your menu by adding complementary items such as drinks, snacks, or new flavors. This gives customers more choices and increases your average sales per transaction. Make sure any new product maintains quality and fits your target market’s preferences.'
                ],
                [
                    'title' => 'Expanding to multiple locations', 
                    'description' => 'After achieving consistent success in one location, you can grow your business by opening additional carts in other high-traffic areas. This allows you to reach more customers and increase your overall income. Before expanding, ensure your operations, supply system, and management are well-organized to maintain quality across all locations.'
                ]
            ]
        ];
    }
}