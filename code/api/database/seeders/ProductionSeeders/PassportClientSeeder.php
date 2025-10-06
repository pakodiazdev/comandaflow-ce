<?php

namespace Database\Seeders\ProductionSeeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class PassportClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🔐 Setting up Passport clients...\n";
        
        $clientRepository = new ClientRepository();
        
        // Create Personal Access Client if not exists
        $personalClient = Client::where('name', 'CF Auth Personal Access Client')->first();
        if (!$personalClient) {
            $personalClient = $clientRepository->createPersonalAccessClient(
                null, 
                'CF Auth Personal Access Client', 
                config('app.url')
            );
            echo "   ✅ Created Personal Access Client\n";
        } else {
            echo "   ✅ Personal Access Client already exists\n";
        }
        
        // Create Password Grant Client if not exists
        $passwordClient = Client::where('name', 'CF Auth Password Grant Client')->first();
        if (!$passwordClient) {
            $passwordClient = $clientRepository->createPasswordGrantClient(
                null, 
                'CF Auth Password Grant Client', 
                config('app.url')
            );
            echo "   ✅ Created Password Grant Client\n";
        } else {
            echo "   ✅ Password Grant Client already exists\n";
        }
        
        echo "✅ Passport clients setup completed!\n";
    }
}