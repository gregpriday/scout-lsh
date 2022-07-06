<?php

namespace SiteOrigin\ScoutLSH\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SiteOrigin\ScoutLSH\Facades\TextEncoder;

class TestTextEncoder extends TestCase
{
    use RefreshDatabase;

    public function test_text_encoder_cache()
    {
        $encoded = TextEncoder::encode([
            'this is some text',
            'this is some more text',
            'here is some final text',
        ], true);

        // Now we will encode the text and make sure we receive the correct inputs
        $encoded2 = TextEncoder::encode([
            'this is some text',
            'this is some different text',
            'here is some final text',
        ], true);

        // Make sure that we don't call text encodeTexts again
        $encoded3 = TextEncoder::encode([
            'this is some different text',
        ], true);

        $this->assertEquals(json_encode($encoded[0]), json_encode($encoded2[0]));
        $this->assertEquals(json_encode($encoded[2]), json_encode($encoded2[2]));
        $this->assertEquals(json_encode($encoded2[1]), json_encode($encoded3[0]));
    }
}
