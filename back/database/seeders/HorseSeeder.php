<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Horse;
use Illuminate\Database\Seeder;

class HorseSeeder extends Seeder
{
    /**
     * Source photos live in the Vue app's public folder and are the owner's real
     * photography — they are copied into the media library, never moved (see
     * preservingOriginal below), so the originals stay where they are.
     *
     * Every horse below is named from the watermark burned into its own photo,
     * and only photos that positively identify that horse are attached. Photos
     * whose horse could not be identified are listed in UNASSIGNED_PHOTOS rather
     * than guessed at — a wrong name on a sales listing is worse than no listing.
     */
    private const IMAGE_SOURCE = __DIR__.'/../../../front/public/images';

    /**
     * Real horse photos that carry no readable name, plus the stud's promo
     * collage. The owner should attach these to the right horse in the dashboard.
     */
    private const UNASSIGNED_PHOTOS = [
        '725621135_1541851770949941_8019762622346553312_n.jpeg', // bay
        '759727852_1571350641304057_1469382873216134032_n.jpeg', // grey
        '725947103_1990280771582032_3266527380658937234_n.jpeg', // grey headshot
        '725678875_1452361810265445_1529025466700469851_n.jpeg', // dark bay with handler
        '722364276_1378420444179577_8882095035186689772_n.jpeg', // "El Baraa stud" promo collage, not one horse
    ];

    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');

        foreach ($this->horses() as $data) {
            $images = $data['images'];
            $slugs = $data['categories'];
            unset($data['images'], $data['categories']);

            $horse = Horse::updateOrCreate(['name_en' => $data['name_en']], $data);

            $horse->categories()->sync(
                collect($slugs)->map(fn (string $slug) => $categories[$slug])->all()
            );

            if ($horse->getMedia(Horse::GALLERY_COLLECTION)->isEmpty()) {
                foreach ($images as $image) {
                    $path = self::IMAGE_SOURCE.'/'.$image;

                    if (! is_file($path)) {
                        $this->command->warn("Missing seed image: {$image}");

                        continue;
                    }

                    $horse->addMedia($path)
                        ->preservingOriginal()
                        ->toMediaCollection(Horse::GALLERY_COLLECTION);
                }
            }
        }

        $this->command->info('Unassigned photos (attach in dashboard): '.count(self::UNASSIGNED_PHOTOS));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function horses(): array
    {
        return [
            [
                'name_en' => 'Samraa El Baraa',
                'name_ar' => 'سمراء البراء',
                'breed_en' => 'Straight Egyptian Arabian',
                'breed_ar' => 'عربي مصري أصيل',
                'gender' => 'female',
                'date_of_birth' => '2021-03-14',
                'description_en' => 'A striking black Straight Egyptian mare with exceptional presence and movement. Refined head, long arched neck, and a floating trot that turns heads in the show ring.',
                'description_ar' => 'فرس سوداء من السلالة المصرية الأصيلة، ذات حضور استثنائي وحركة مميزة. رأس مصفّى ورقبة طويلة مقوسة، وخطوة تلفت الأنظار في الحلبة.',
                'price' => 185000,
                'currency' => 'EGP',
                'status' => 'available',
                'is_featured' => true,
                'categories' => ['sale', 'breeding'],
                'images' => ['612612968_33687602540831053_7359887036261105813_n.jpg'],
            ],
            [
                'name_en' => 'Sultana',
                'name_ar' => 'سلطانة',
                'breed_en' => 'Straight Egyptian Arabian',
                'breed_ar' => 'عربي مصري أصيل',
                'gender' => 'female',
                'date_of_birth' => '2022-04-09',
                'description_en' => 'Jet-black mare with a bright star, a deep jowl and large dark eyes. Elegant throughout, with the dry, chiselled head the Egyptian lines are known for.',
                'description_ar' => 'فرس سوداء ناصعة بغرة بيضاء، فك عميق وعيون كبيرة داكنة. أنيقة التكوين برأس جاف ومنحوت يميّز الخطوط المصرية.',
                'price' => null,
                'status' => 'available',
                'is_featured' => true,
                'categories' => ['sale', 'breeding'],
                'images' => ['730584842_1435134921754242_5231241533222015163_n.jpeg'],
            ],
            [
                'name_en' => 'Nasser El Baraa',
                'name_ar' => 'ناصر البراء',
                'breed_en' => 'Straight Egyptian Arabian',
                'breed_ar' => 'عربي مصري أصيل',
                'gender' => 'male',
                'date_of_birth' => '2020-05-02',
                'description_en' => 'Grey stallion of classic Egyptian type — deep jowl, large dark eyes and a proud carriage. A consistent sire of correct, typey foals.',
                'description_ar' => 'حصان أشهب من النوع المصري الكلاسيكي — فك عميق وعيون كبيرة داكنة وقوام مرفوع. ينتج مهوراً صحيحة التكوين ومميزة الشكل.',
                'price' => null,
                'status' => 'available',
                'is_featured' => true,
                'categories' => ['mating', 'breeding'],
                'images' => ['616565689_33814184764839496_5653523334166532694_n.jpg'],
            ],
            [
                'name_en' => 'Zain',
                'name_ar' => 'زين',
                'breed_en' => 'Straight Egyptian Arabian',
                'breed_ar' => 'عربي مصري أصيل',
                'gender' => 'male',
                'date_of_birth' => '2022-01-20',
                'description_en' => 'Bay stallion with strong bone, a powerful shoulder and effortless self-carriage. Bold, willing temperament under handling.',
                'description_ar' => 'حصان كميت قوي البنية، كتف قوي وحمل ذاتي متزن. طبع جريء ومتعاون في التعامل.',
                'price' => 250000,
                'currency' => 'EGP',
                'status' => 'available',
                'is_featured' => false,
                'categories' => ['sale', 'mating', 'breeding'],
                'images' => ['612230213_33739970855594221_2160425073495852291_n.jpg'],
            ],
            [
                'name_en' => 'D Anya El Baraa',
                'name_ar' => 'دي أنيا البراء',
                'breed_en' => 'Straight Egyptian Arabian',
                'breed_ar' => 'عربي مصري أصيل',
                'gender' => 'female',
                'date_of_birth' => '2023-06-18',
                'description_en' => 'Young bay filly of the stable\'s own breeding, showing balance and a light, athletic way of moving. One to watch as she matures.',
                'description_ar' => 'مهرة كميت صغيرة من إنتاج المربط، تُظهر اتزاناً وحركة خفيفة رياضية. من الخيول الواعدة مع اكتمال نموها.',
                'price' => null,
                'status' => 'available',
                'is_featured' => true,
                'categories' => ['breeding'],
                'images' => ['724350299_1092162810658157_8771182491514361779_n.jpeg'],
            ],
            [
                // Two photos of the same chestnut mare, but her name is not in either
                // watermark — descriptive placeholder for the owner to rename.
                'name_en' => 'Chestnut Mare',
                'name_ar' => 'الفرس الكستنائية',
                'breed_en' => 'Straight Egyptian Arabian',
                'breed_ar' => 'عربي مصري أصيل',
                'gender' => 'female',
                'date_of_birth' => '2021-09-08',
                'description_en' => 'Elegant chestnut mare with a flaxen mane and tail, clean throatlatch and a smooth topline.',
                'description_ar' => 'فرس كستنائية أنيقة بعرف وذيل فاتحين، رقبة نظيفة وخط ظهر انسيابي.',
                'price' => null,
                'status' => 'reserved',
                'is_featured' => false,
                'categories' => ['breeding'],
                'images' => [
                    '721104872_1529315975244138_2773861928390700189_n.jpeg',
                    '764925234_1394687099426033_7425239511347304196_n.jpeg',
                ],
            ],
        ];
    }
}
