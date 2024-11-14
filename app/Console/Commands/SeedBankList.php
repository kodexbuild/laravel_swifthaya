<?php

namespace App\Console\Commands;

use App\Models\Bank;
use Illuminate\Console\Command;

class SeedBankList extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'app:seed-bank-list';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description';

  /**
   * Execute the console command.
   */

  // SeedBankList command to fetch banks and save to database
  public function handle()
  {
    $SECRET_KEY = env("PAYSTACK_SECRET_KEY");

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.paystack.co/bank",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer $SECRET_KEY",
        "Cache-Control: no-cache",
      ),
    ));

    $response = json_decode(curl_exec($curl), true);
    $err = curl_error($curl);

    curl_close($curl);

    if (!$response || !$response['status']) {
      $this->error('Failed to fetch bank list from Paystack.');
      return;
    }

    foreach ($response['data'] as $bank) {
      Bank::updateOrCreate(
        ['code' => $bank['code']],
        ['name' => $bank['name']]
      );
    }

    $this->info('Bank list seeded successfully.');
  }
}
