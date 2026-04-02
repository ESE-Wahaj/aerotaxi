<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Airport;
use App\Models\Faq;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')]
        );

        Admin::create([
            'name' => 'Admin',
            'email' => 'admin@aerotaxi.com',
            'password' => Hash::make('admin123'),
        ]);

        $this->seedAirports();
        $this->seedVehicles();
        $this->seedFaqs();
    }

    private function seedAirports(): void
    {
        $airports = [
            ['code' => 'LHR', 'name' => 'London Heathrow Airport', 'city' => 'London', 'description' => "UK's busiest airport and major international hub", 'image' => '/images/airports/lhr.png', 'sort_order' => 1],
            ['code' => 'LGW', 'name' => 'London Gatwick Airport', 'city' => 'London', 'description' => "London's second-largest airport serving millions", 'image' => '/images/airports/lgw.svg', 'sort_order' => 2],
            ['code' => 'STN', 'name' => 'London Stansted Airport', 'city' => 'London', 'description' => 'Major hub for European low-cost carriers', 'image' => '/images/airports/stn.png', 'sort_order' => 3],
            ['code' => 'LTN', 'name' => 'London Luton Airport', 'city' => 'London', 'description' => 'Popular choice for budget airlines and European flights', 'image' => '/images/airports/ltn.png', 'sort_order' => 4],
            ['code' => 'LCY', 'name' => 'London City Airport', 'city' => 'London', 'description' => 'Convenient airport in East London for business travel', 'image' => '/images/airports/lcy.png', 'sort_order' => 5],
            ['code' => 'SEN', 'name' => 'London Southend Airport', 'city' => 'London', 'description' => 'Compact airport serving London and the South East', 'image' => '/images/airports/sen.png', 'sort_order' => 6],
            ['code' => 'MAN', 'name' => 'Manchester Airport', 'city' => 'Manchester', 'description' => "UK's third-busiest airport serving the North", 'image' => '/images/airports/man.svg', 'sort_order' => 7],
            ['code' => 'EDI', 'name' => 'Edinburgh Airport', 'city' => 'Edinburgh', 'description' => "Scotland's busiest airport serving the capital", 'image' => '/images/airports/edi.svg', 'sort_order' => 8],
            ['code' => 'BHX', 'name' => 'Birmingham Airport', 'city' => 'Birmingham', 'description' => "Central England's premier international airport", 'image' => '/images/airports/bhx.svg', 'sort_order' => 9],
            ['code' => 'BRS', 'name' => 'Bristol Airport', 'city' => 'Bristol', 'description' => 'Gateway to the South West of England', 'image' => '/images/airports/brs.svg', 'sort_order' => 10],
            ['code' => 'NCL', 'name' => 'Newcastle Airport', 'city' => 'Newcastle', 'description' => "Newcastle's main international airport", 'image' => '/images/airports/ncl.svg', 'sort_order' => 11],
            ['code' => 'BFS', 'name' => 'Belfast International Airport', 'city' => 'Belfast', 'description' => "Northern Ireland's main international airport", 'image' => '/images/airports/bfs.png', 'sort_order' => 12],
        ];

        foreach ($airports as $airport) {
            Airport::create($airport);
        }
    }

    private function seedVehicles(): void
    {
        $vehicles = [
            ['name' => 'Saloon', 'slug' => 'saloon', 'price' => 34.00, 'short_base' => 32.64, 'short_per_mile' => 2.57, 'long_base' => 54.14, 'long_per_mile' => 1.17, 'passengers' => 3, 'suitcases' => 3, 'hand_luggage_note' => 'or 4 with hand luggage only', 'image' => '/images/vehicles/saloon.svg', 'description' => 'Perfect for business trips and solo travelers', 'car_model' => 'Prius or similar', 'sort_order' => 1],
            ['name' => 'Executive', 'slug' => 'executive', 'price' => 45.00, 'short_base' => 43.07, 'short_per_mile' => 3.41, 'long_base' => 69.89, 'long_per_mile' => 1.64, 'passengers' => 3, 'suitcases' => 3, 'hand_luggage_note' => 'or 4 with hand luggage', 'image' => '/images/vehicles/executive.svg', 'description' => 'Premium comfort for discerning travelers', 'car_model' => 'Mercedes E-Class or similar', 'sort_order' => 2],
            ['name' => 'Estate', 'slug' => 'estate', 'price' => 39.00, 'short_base' => 36.96, 'short_per_mile' => 2.94, 'long_base' => 61.76, 'long_per_mile' => 1.35, 'passengers' => 4, 'suitcases' => 4, 'hand_luggage_note' => null, 'image' => '/images/vehicles/estate.svg', 'description' => 'Extra space for luggage and comfort', 'car_model' => 'VW Passat or similar', 'sort_order' => 3],
            ['name' => 'People Carrier', 'slug' => 'people-carrier', 'price' => 45.00, 'short_base' => 44.81, 'short_per_mile' => 3.32, 'long_base' => 68.30, 'long_per_mile' => 1.69, 'passengers' => 5, 'suitcases' => 5, 'hand_luggage_note' => 'or 6 with hand luggage', 'image' => '/images/vehicles/people-carrier.svg', 'description' => 'Ideal for families and small groups', 'car_model' => 'Ford Galaxy or similar', 'sort_order' => 4],
            ['name' => 'Executive People Carrier', 'slug' => 'executive-people-carrier', 'price' => 64.00, 'short_base' => 60.59, 'short_per_mile' => 4.57, 'long_base' => 99.89, 'long_per_mile' => 2.20, 'passengers' => 5, 'suitcases' => 5, 'hand_luggage_note' => 'or 6 with hand luggage', 'image' => '/images/vehicles/people-carrier.svg', 'description' => 'Luxury travel for groups', 'car_model' => 'Mercedes V-Class or similar', 'sort_order' => 5],
            ['name' => 'Minibus', 'slug' => 'minibus', 'price' => 67.00, 'short_base' => 71.85, 'short_per_mile' => 3.90, 'long_base' => 96.78, 'long_per_mile' => 1.52, 'passengers' => 8, 'suitcases' => 8, 'hand_luggage_note' => null, 'image' => '/images/vehicles/minibus.svg', 'description' => 'Perfect for larger groups', 'car_model' => 'Mercedes Sprinter or similar', 'sort_order' => 6],
            ['name' => '16Pax', 'slug' => '16pax', 'price' => 155.00, 'short_base' => 140.17, 'short_per_mile' => 8.62, 'long_base' => 223.71, 'long_per_mile' => 2.47, 'passengers' => 16, 'suitcases' => 16, 'hand_luggage_note' => null, 'image' => '/images/vehicles/pax16.svg', 'description' => 'Maximum capacity for large parties', 'car_model' => 'Ford Transit or similar', 'sort_order' => 7],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::create($vehicle);
        }
    }

    private function seedFaqs(): void
    {
        $faqs = [
            [
                'question' => 'Where can I find my booking reference?',
                'answer' => 'Your booking reference was sent to you in your confirmation email. You can find it at the top of the booking confirmation.',
                'sort_order' => 1,
            ],
            [
                'question' => 'Can I make changes to my booking?',
                'answer' => "Yes, you can make changes to your booking up to 24 hours before the scheduled pick-up time. Access the 'My Booking' section on our website with your booking reference and email address.",
                'sort_order' => 2,
            ],
            [
                'question' => 'How can I cancel my booking?',
                'answer' => 'To cancel your booking, please contact us at support@airporttaxihub.com with your booking reference. Cancellations made more than 24 hours before the scheduled pick-up time are eligible for a full refund minus card processing fees.',
                'sort_order' => 3,
            ],
            [
                'question' => 'How can I inform you about a problem with my booking?',
                'answer' => 'Please email us at support@airporttaxihub.com with your booking reference and a description of the problem. We aim to acknowledge all complaints within five working days.',
                'sort_order' => 4,
            ],
            [
                'question' => 'How can I inform you about my flight delay?',
                'answer' => 'Your driver automatically tracks your flight, so they will be aware of any delays. If you need to contact your driver directly, you can find the supplier details in your booking confirmation email. For further assistance, contact us at support@airporttaxihub.com.',
                'sort_order' => 5,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }
    }
}
