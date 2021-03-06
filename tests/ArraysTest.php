<?php

declare(strict_types=1);

use Atomastic\Arrays\Arrays;

test('test create() method', function (): void {
    $this->assertEquals(new Arrays(), Arrays::create());
});

test('test arrays() helper', function (): void {
    $this->assertEquals(Arrays::create(), arrays());
});

test('test all() method', function (): void {
    $this->assertEquals(['SG-1', 'SG-2'], Arrays::create(['SG-1', 'SG-2'])->all());
});

test('test set() method', function (): void {
    $this->assertEquals(
        ['movies' => ['SG-1' => ['stars' => ['Jack', 'Daniel', 'Sam']]]],
        Arrays::create([])->set('movies', ['SG-1' => ['stars' => ['Jack', 'Daniel', 'Sam']]])->all()
    );

    $this->assertEquals(
        ['movies' => ['SG-1' => ['stars' => ['Jack', 'Daniel', 'Sam']]]],
        Arrays::create([])->set('movies.SG-1.stars', ['Jack', 'Daniel', 'Sam'])->all()
    );

    $this->assertEquals(
        ['movies' => ['SG-1' => ['stars' => ['Jack', 'Daniel', 'Sam']]]],
        Arrays::create()->set('movies.SG-1.stars', ['Jack', 'Daniel', 'Sam'])->all()
    );

    $this->assertEquals(
        ['movies' => ['SG-1' => ['stars' => []]]],
        Arrays::create()->set('movies.SG-1.stars', [])->all()
    );
});

test('test get() method', function (): void {
    $this->assertEquals(
        ['Jack', 'Daniel', 'Sam'],
        Arrays::create(['stars' => ['Jack', 'Daniel', 'Sam']])->get('stars')
    );
    $this->assertEquals(
        ['Jack', 'Daniel', 'Sam'],
        Arrays::create(['movies' => ['SG-1' => ['stars' => ['Jack', 'Daniel', 'Sam']]]])->get('movies.SG-1.stars')
    );
    $this->assertEquals(
        ['test'],
        Arrays::create(['movies' => ['SG-1' => ['stars' => ['Jack', 'Daniel', 'Sam']]]])->get('film.scores', ['test'])
    );
});

test('test has() method', function (): void {
    $this->assertTrue(Arrays::create(['movies' => ['SG-1' => ['stars' => ['Jack', 'Daniel', 'Sam'], 'score' => ['5', '4']]]])->has(['movies.SG-1.stars', 'movies.SG-1.score']));
    $this->assertTrue(Arrays::create(['movies' => ['SG-1' => ['stars' => ['Jack', 'Daniel', 'Sam']]]])->has('movies.SG-1.stars'));
    $this->assertFalse(Arrays::create(['movies' => ['SG-1' => ['Jack', 'Daniel', 'Sam']]])->has('movies.SG-1.scores'));

    $this->assertTrue(Arrays::create(['movies' => [0 => ['stars' => ['Jack', 'Daniel', 'Sam']]]])->has('movies.0.stars'));
    $this->assertFalse(Arrays::create(['movies' => [0 => ['stars' => ['Jack', 'Daniel', 'Sam']]]])->has('movies.0.scores'));

    $this->assertFalse(Arrays::create([])->has([null]));
    $this->assertTrue(Arrays::create(['' => 'foobar'])->has(''));
    $this->assertTrue(Arrays::create(['' => 'foobar'])->has(['']));
    $this->assertFalse(Arrays::create([''])->has(''));
    $this->assertFalse(Arrays::create([])->has(''));
    $this->assertFalse(Arrays::create([])->has(['']));
});

test('test delete() method', function (): void {
    $array = Arrays::create(['film' => ['stars' => ['Jack', 'Daniel', 'Sam']]]);
    $array->delete('film.stars');
    $this->assertFalse($array->has('film.stars'));

    $array = Arrays::create(['film' => ['stars' => ['Jack', 'Daniel', 'Sam'], 'score' => ['5', '4']]]);
    $array->delete('film.stars');
    $array->delete('film.score');
    $this->assertFalse($array->has(['film.stars', 'film.score']));
});

test('test dot() method', function (): void {
    $this->assertEquals(
        [
            'movies.the_thin_red_line.title' => 'The Thin Red Line',
            'movies.the_thin_red_line.directed_by' => 'Terrence Malick',
            'movies.the_thin_red_line.produced_by' => 'Robert Michael, Geisler Grant Hill, John Roberdeau',
            'movies.the_thin_red_line.decription' => 'Adaptation of James Jones autobiographical 1962 novel, focusing on the conflict at Guadalcanal during the second World War.',
        ],
        Arrays::create([
            'movies' => [
                'the_thin_red_line' => [
                    'title' => 'The Thin Red Line',
                    'directed_by' => 'Terrence Malick',
                    'produced_by' => 'Robert Michael, Geisler Grant Hill, John Roberdeau',
                    'decription' => 'Adaptation of James Jones autobiographical 1962 novel, focusing on the conflict at Guadalcanal during the second World War.',
                ],
            ],
        ])->dot()->all()
    );
});

test('test undot() method', function (): void {
    $this->assertEquals(
        [
            'movies' => [
                'the_thin_red_line' => [
                    'title' => 'The Thin Red Line',
                    'directed_by' => 'Terrence Malick',
                    'produced_by' => 'Robert Michael, Geisler Grant Hill, John Roberdeau',
                    'decription' => 'Adaptation of James Jones autobiographical 1962 novel, focusing on the conflict at Guadalcanal during the second World War.',
                ],
            ],
        ],
        Arrays::create([
            'movies.the_thin_red_line.title' => 'The Thin Red Line',
            'movies.the_thin_red_line.directed_by' => 'Terrence Malick',
            'movies.the_thin_red_line.produced_by' => 'Robert Michael, Geisler Grant Hill, John Roberdeau',
            'movies.the_thin_red_line.decription' => 'Adaptation of James Jones autobiographical 1962 novel, focusing on the conflict at Guadalcanal during the second World War.',
        ])->undot()->all()
    );
});


test('test flush() method', function (): void {
    $arrays = Arrays::create()->set('stars', ['Jack', 'Daniel', 'Sam']);
    $arrays->flush();
    $this->assertEquals([], $arrays->all());
});

test('test sortBySubKey() method', function (): void {
    // Default
    $arrays_original = [
        0 => ['title' => 'Post 1'],
        1 => ['title' => 'Post 2'],
    ];

    $arrays_result = Arrays::create([
        1 => ['title' => 'Post 2'],
        0 => ['title' => 'Post 1'],
    ])->sortBySubKey('title')->all();

    $array_equal = static function ($a, $b) {
        return serialize($a) === serialize($b);
    };

    $this->assertTrue($array_equal($arrays_original, $arrays_result));

    // SORT ASC
    $arrays_original = [
        0 => ['title' => 'Post 1'],
        1 => ['title' => 'Post 2'],
    ];

    $arrays_result = Arrays::create([
        1 => ['title' => 'Post 2'],
        0 => ['title' => 'Post 1'],
    ])->sortBySubKey('title', 'ASC')->all();

    $array_equal = static function ($a, $b) {
        return serialize($a) === serialize($b);
    };

    $this->assertTrue($array_equal($arrays_original, $arrays_result));

    // SORT DESC
    $arrays_original = [
        1 => ['title' => 'Post 2'],
        0 => ['title' => 'Post 1'],
    ];

    $arrays_result = Arrays::create([
        1 => ['title' => 'Post 2'],
        0 => ['title' => 'Post 1'],
    ])->sortBySubKey('title', 'DESC')->all();

    $array_equal = static function ($a, $b) {
        return serialize($a) === serialize($b);
    };

    $this->assertTrue($array_equal($arrays_original, $arrays_result));

    $arrays_original = Arrays::create([
                            'movies' => [
                               'the_thin_red_line' => [
                                   'title' => 'The Thin Red Line',
                                   'directed_by' => 'Terrence Malick',
                                   'produced_by' => 'Robert Michael, Geisler Grant Hill, John Roberdeau',
                                   'decription' => 'Adaptation of James Jones autobiographical 1962 novel, focusing on the conflict at Guadalcanal during the second World War.'
                               ],
                               'bad_times_at_the_el_royale' => [
                                   'title' => 'Bad Times at the El Royale',
                                   'directed_by' => 'Drew Goddard',
                                   'produced_by' => 'Drew Goddard, Steve Asbell',
                                   'decription' => 'Early 1970s. Four strangers check in at the El Royale Hotel. The hotel is deserted, staffed by a single desk clerk. Some of the new guests reasons for being there are less than innocent and some are not who they appear to be.'
                               ]
                            ]
                        ]);
    $movies = $arrays_original->get('movies');
    $arrays_result = Arrays::create($movies)->sortBySubKey('title', 'DESC')->all();

    $array_equal = static function ($a, $b) {
        return serialize($a) === serialize($b);
    };

    $this->assertTrue($array_equal($movies, $arrays_result));

    $arrays_original = Arrays::create([
                            'movies' => [
                               'the_thin_red_line' => [
                                   'title' => 'The Thin Red Line',
                                   'directed_by' => 'Terrence Malick',
                                   'produced_by' => 'Robert Michael, Geisler Grant Hill, John Roberdeau',
                                   'decription' => 'Adaptation of James Jones autobiographical 1962 novel, focusing on the conflict at Guadalcanal during the second World War.'
                               ],
                               'bad_times_at_the_el_royale' => [
                                   'title' => 'Bad Times at the El Royale',
                                   'directed_by' => 'Drew Goddard',
                                   'produced_by' => 'Drew Goddard, Steve Asbell',
                                   'decription' => 'Early 1970s. Four strangers check in at the El Royale Hotel. The hotel is deserted, staffed by a single desk clerk. Some of the new guests reasons for being there are less than innocent and some are not who they appear to be.'
                               ]
                            ]
                        ]);
    $movies = $arrays_original->get('movies');
    $arrays_result = Arrays::create($movies)->sortBySubKey('title', 'ASC')->all();

    $array_equal = static function ($a, $b) {
        return serialize($a) === serialize($b);
    };

    $this->assertFalse($array_equal($movies, $arrays_result));

});

test('test count() method', function (): void {
    $this->assertEquals(3, Arrays::create(['Jack', 'Daniel', 'Sam'])->count());
    $this->assertEquals(1, Arrays::create(['names' => ['Jack', 'Daniel', 'Sam']])->count());
    $this->assertEquals(2, Arrays::create(['names' => ['Jack', 'Daniel', 'Sam'], 'tags' => ['star', 'movie']])->count('tags'));
    $this->assertEquals(2, Arrays::create(['collection' => ['names' => ['Jack', 'Daniel', 'Sam'], 'tags' => ['star', 'movie']]])->count('collection.tags'));
});

test('test divide() method', function (): void {
    $this->assertEquals([['name'], ['Daniel']], Arrays::create(['name' => 'Daniel'])->divide());
});

test('test isEqual() method', function (): void {
    $this->assertTrue(Arrays::create([])->isEqual([]));
    $this->assertTrue(Arrays::create(['name' => 'Daniel'])->isEqual(['name' => 'Daniel']));
    $this->assertFalse(Arrays::create(['name' => 'Daniel'])->isEqual(['name' => 'Sam']));
});

test('test isAssoc() method', function (): void {
    $this->assertTrue(Arrays::create(['a' => 'a', 0 => 'b'])->isAssoc());
    $this->assertTrue(Arrays::create([1 => 'a', 0 => 'b'])->isAssoc());
    $this->assertTrue(Arrays::create([1 => 'a', 2 => 'b'])->isAssoc());
    $this->assertFalse(Arrays::create([0 => 'a', 1 => 'b'])->isAssoc());
    $this->assertFalse(Arrays::create(['a', 'b'])->isAssoc());
});

test('test toQuery() method', function (): void {
    $this->assertEquals('', Arrays::create([])->toQuery());
    $this->assertEquals('foo=bar', Arrays::create(['foo' => 'bar'])->toQuery());
    $this->assertEquals('foo=bar&bar=baz', Arrays::create(['foo' => 'bar', 'bar' => 'baz'])->toQuery());
    $this->assertEquals('foo=bar&bar=1', Arrays::create(['foo' => 'bar', 'bar' => true])->toQuery());
    $this->assertEquals('foo=bar', Arrays::create(['foo' => 'bar', 'bar' => null])->toQuery());
    $this->assertEquals('foo=bar&bar=', Arrays::create(['foo' => 'bar', 'bar' => ''])->toQuery());
});

test('test toArray() method', function (): void {
    $this->assertEquals(['SG-1', 'SG-2'], Arrays::create(['SG-1', 'SG-2'])->toArray());
});

test('test toJson() method', function (): void {
    $this->assertEquals('["SG-1","SG-2"]', Arrays::create(['SG-1', 'SG-2'])->toJson());
    $this->assertEquals('{"foo":"bar","bar":"baz"}', Arrays::create(['foo' => 'bar', 'bar' => 'baz'])->toJson());
});

test('test toString() method', function (): void {
    $this->assertEquals('SG-1,SG-2', Arrays::create(['SG-1', 'SG-2'])->toString());
    $this->assertEquals('foo1,bar1,foo2,bar2', Arrays::create(['foo1' => 'bar1', 'foo2' => 'bar2'])->toString(',', true));
    $this->assertEquals('foo1,bar1,foo2,bar2', Arrays::create(['foo1' => 'bar1 ', 'foo2' => ' bar2'])->toString(',', true, true));
});

test('test first() method', function (): void {
    $this->assertEquals('SG-1', Arrays::create(['SG-1', 'SG-2'])->first());
    $this->assertEquals('bar1', Arrays::create(['foo1' => 'bar1', 'foo2' => 'bar2'])->first());
});

test('test firstKey() method', function (): void {
    $this->assertEquals(0, Arrays::create(['SG-1', 'SG-2'])->firstKey());
    $this->assertEquals('foo1', Arrays::create(['foo1' => 'bar1', 'foo2' => 'bar2'])->firstKey());
});

test('test last() method', function (): void {
    $this->assertEquals('SG-2', Arrays::create(['SG-1', 'SG-2'])->last());
    $this->assertEquals('bar2', Arrays::create(['foo1' => 'bar1', 'foo2' => 'bar2'])->last());
});

test('test lastKey() method', function (): void {
    $this->assertEquals(1, Arrays::create(['SG-1', 'SG-2'])->lastKey());
    $this->assertEquals('foo2', Arrays::create(['foo1' => 'bar1', 'foo2' => 'bar2'])->lastKey());
});

test('test pull() method', function (): void {
    $array = Arrays::create(['movies' => ['SG-1', 'Mulan']]);
    $this->assertTrue($array->has('movies.1'));

    $array->pull('movies.1');
    $this->assertFalse($array->has('movies.1'));
});

test('test append() method', function (): void {
    $this->assertEquals([0 => 'foo', 1 => 'bar'], Arrays::create(['foo'])->append('bar')->toArray());
    $this->assertEquals([0 => 'foo', 1 => ['bar']], Arrays::create(['foo'])->append(['bar'])->toArray());
    $this->assertEquals([0 => 'foo', 1 => ['bar', 'foo']], Arrays::create(['foo'])->append(['bar', 'foo'])->toArray());
});

test('test prepend() method', function (): void {
    $this->assertEquals([0 => 'bar', 1 => 'foo'], Arrays::create(['foo'])->prepend('bar')->toArray());
    $this->assertEquals([0 => ['bar'], 1 => 'foo'], Arrays::create(['foo'])->prepend(['bar'])->toArray());
    $this->assertEquals([0 => ['bar', 'foo'], 1 => 'foo'], Arrays::create(['foo'])->prepend(['bar', 'foo'])->toArray());
});

test('test chunk() method', function (): void {
    $this->assertEquals([0 => [0 => 'a', 1 => 'b']],
                        Arrays::create(['a', 'b'])->chunk(2)->toArray());
    $this->assertEquals([0 => [0 => 'a', 1 => 'b']],
                        Arrays::create(['a' => 'a', 'b' => 'b'])->chunk(2)->toArray());
    $this->assertEquals([0 => ['a' => 'a', 'b' => 'b']],
                        Arrays::create(['a' => 'a', 'b' => 'b'])->chunk(2, true)->toArray());
});
