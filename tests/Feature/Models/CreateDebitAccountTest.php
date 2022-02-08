<?php

namespace GloCurrency\ZenithBank\Tests\Feature\Models\DebitAccount;

use GloCurrency\ZenithBank\Tests\FeatureTestCase;
use GloCurrency\ZenithBank\Models\DebitAccount;

class CreateDebitAccountTest extends FeatureTestCase
{
    /** @test */
    public function it_cannot_be_created_with_the_same_country_code_and_currency_code()
    {
        DebitAccount::factory()->create([
            'country_code' => 'NGA',
            'currency_code' => 'USD',
        ]);

        try {
            DebitAccount::factory()->create([
                'country_code' => 'NGA',
                'currency_code' => 'USD',
            ]);
        } catch (\Throwable $th) {
            $this->assertInstanceOf(\PDOException::class, $th);
            $this->assertCount(1, DebitAccount::where([
                'country_code' => 'NGA',
                'currency_code' => 'USD',
            ])->get());
            return;
        }

        $this->fail('Exception was not thrown');
    }
}
