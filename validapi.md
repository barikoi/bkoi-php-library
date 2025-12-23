$result = Barikoi::autocomplete('Dhanmondi', [
                'bangla'=>true
            ]);

$result=Barikoi::calculateRoute([
                ['longitude' => 90.3572, 'latitude' => 23.8067],
                ['longitude' => 90.3680, 'latitude' => 23.8100],
            ],
            [
                'profile' => 'foot',
                'geometries' => 'polyline6',
            ]);

$options = [
            'country_code'=>'BD',
            'district' => true,
            'post_code' => true,
            'country' => true,
            'sub_district' => true,
            'union' => true,
            'pauroshova' => true,
            'location_type' => true,
            'division' => true,
            'address' => true,
            'area' => true,
            'bangla' => true,
            'thana' => true,
        ];
        try {
            $result = Barikoi::reverseGeocode(90.3572, 23.8067, $options);
            $this->info(json_encode($result, JSON_PRETTY_PRINT));
        }

$result=Barikoi::routeOverview([
                ['longitude' => 90.3572, 'latitude' => 23.8067],
                ['longitude' => 90.3680, 'latitude' => 23.8100],
            ],
            [
                'profile' => 'car',
                'geometries' => 'polyline',
            ]);


$result = Barikoi::geocode('D');

$result = Barikoi::nearby(90.38305163, 23.87188719, 0.5, 2);

$result=Barikoi::searchPlace('Dhanmondi');

 $result=Barikoi::snapToRoad(23.8067, 70.3572);