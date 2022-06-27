<?php

namespace SiteOrigin\ScoutLSH\Tests\database\Seeders;

use Illuminate\Database\Seeder;
use SiteOrigin\ScoutLSH\Tests\Models\Listing;

class RealEstateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Listing::create([
            'title' => '4 Bedroom House for Sale in Lake Michelle Security and Eco Estate',
            'description' => "This classic home is well-built, well-designed and in immaculate condition throughout. Characterised by generous spaces, double-volume ceilings, exposed trusses, easy indoor/outdoor flow and great views - it's an entertainers delight and ideal for the larger family home or dual-living opportunity. Step into the welcoming entrance hall, and the house divides into 2 wings. The main house is all on one level, offering (1) open-plan fitted kitchen with with ample space for all your appliances, center island, separate scullery and walk-in pantry, (2) open-plan dining with picture window looking out to the garden and (3) open-plan lounge with feature wood-burning fireplace - all flowing out to the sunny undercover entertainment deck, sparkling swimming pool, and much-loved garden, abundant with birdlife, which rambles down to the walkway along the pond. All North-facing and sheltered from the prevailing summer winds. The main bedroom (with en-suite) also flows to the garden. The second bedroom (with en-suite) also has a private study. The second wing offers a super-spacious upstairs open-plan kitchenette/dining/lounge flooded with natural light and sliding doors to two view balconies.",
            'area' => 'Lake Michelle',
        ]);

        Listing::create([
            'title' => '2 Bedroom House for Sale in Faerie Knowe',
            'description' => "This beautifully appointed 2 bedroom home is sunny and delightful! Offering a spacious lounge/dining area which is open plan to a well fitted kitchen complete with UCO, gas stove and extractor fan. There is great flow to an easy to maintain private and garden and patio. The two bedrooms are fairly proportioned with built in cupboards and there is a family bathroom with extra-large shower. Single auto garage plumbed for washing machine, featuring a bathroom with toilet, basin and shower.",
            'area' => 'Faerie Knowe',
        ]);

        Listing::create([
            'title' => "4 Bedroom House for Sale in Chapman's Bay Estate",
            'description' => "Perfectly positioned with sweeping views of the Noordhoek beach all the way to False Bay. Taking in the exceptional advantage of mountain views, this spectacular home is perched high up on the Estate in a quiet cul-de-sac. Boasting breath-taking views from all levels, along with a double-volume entertainment area that merges effortlessly with the mountain and surrounds and leads on to a wrap-around patio, perfect for entertaining. The well laid-out kitchen offers premium finishes and includes a Smeg gas hob, electric oven and microwave. There is also a separate scullery.",
            'area' => 'Chapmans Bay',
        ]);

        Listing::create([
            'title' => "Vacant Land / Plot for Sale in Chapman's Bay Estate",
            'description' => "Truly an exceptional property on offer. Erf 4520 has beautiful views towards Kalk Bay and is situated on the very popular Siskin Avenue. Chapman’s Bay Estate is a low density security estate below Ou Kaapse Weg. This well elevated estate backs onto the Table Mountain Reserve and has exceptional views over the Noordhoek valley from Atlantic to the Indian ocean. The layout is spacious and fynbos fills the greenbelts between contemporary homes. The estate is 80% complete. Nearby conveniences include:",
            'area' => 'Chapmans Bay',
        ]);
    }
}
