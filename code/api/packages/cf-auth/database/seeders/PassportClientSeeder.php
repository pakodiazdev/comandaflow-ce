<?php

namespace CF\CE\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\Client;
use Laravel\Passport\PersonalAccessClient;

class PassportClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔑 Creating Passport Personal Access Client...');

        $clientId = env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID', 1);
        $clientSecret = env('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET', null);
        $clientName = env('PASSPORT_PERSONAL_ACCESS_CLIENT_NAME', 'CF Auth Personal Access Client');

        // Check if client already exists
        $existingClient = Client::find($clientId);
        
        if ($existingClient) {
            $this->command->line("   ⚠️  Personal Access Client with ID '{$clientId}' already exists, skipping...");
            return;
        }

        // Create the OAuth client
        $client = Client::create([
            'id' => $clientId,
            'name' => $clientName,
            'secret' => $clientSecret ?: \Illuminate\Support\Str::random(40),
            'provider' => null,
            'redirect' => 'http://localhost',
            'personal_access_client' => true,
            'password_client' => false,
            'revoked' => false,
        ]);

        // Create the personal access client record
        PersonalAccessClient::create([
            'client_id' => $client->id,
        ]);

        $this->command->line("   ✅ Created Personal Access Client:");
        $this->command->line("      ID: {$client->id}");
        $this->command->line("      Name: {$client->name}");
        $this->command->line("      Secret: {$client->secret}");
        
        $this->command->info('💡 Add these to your .env file:');
        $this->command->line("PASSPORT_PERSONAL_ACCESS_CLIENT_ID={$client->id}");
        $this->command->line("PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET={$client->secret}");
        $this->command->line("PASSPORT_PERSONAL_ACCESS_CLIENT_NAME=\"{$client->name}\"");
    }
}