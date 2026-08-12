<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::setMany([
            'about_en' => "El Baraa Arabians is a private Egyptian Arabian stud dedicated to preserving and improving the classic Egyptian Arabian horse. Founded on a love for the breed, our programme focuses on correct conformation, authentic type, and sound, willing temperaments.\n\nEvery horse here is raised with individual attention. We welcome visitors who share our passion — whether you are looking for your next partner, a mating, or simply want to meet the horses.",
            'about_ar' => "مربط البراء مربط خاص للخيول العربية المصرية، مكرّس للحفاظ على الحصان العربي المصري الأصيل وتحسين سلالته. تأسس المربط انطلاقاً من عشق هذه السلالة، ويركّز برنامجنا على صحة التكوين وأصالة النوع والطباع الهادئة المتعاونة.\n\nكل حصان لدينا يحظى برعاية واهتمام شخصي. نرحّب بالزوار الذين يشاركوننا هذا الشغف — سواء كنت تبحث عن رفيقك القادم، أو عن تلقيح، أو ترغب فقط في لقاء الخيول.",
            'phone' => '+20 100 000 0000',
            'whatsapp' => '201000000000',
            'email' => 'info@elbaraa-arabians.com',
            'address_en' => 'Egypt',
            'address_ar' => 'مصر',
            'facebook' => '',
            'instagram' => '',
        ]);
    }
}
