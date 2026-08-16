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
            // Placeholder machine-assisted translation — needs native-speaker review before launch.
            'about_zh' => "巴拉阿拉伯马场是一家私人埃及阿拉伯马场，致力于保护和提升纯种埃及阿拉伯马。马场因对这一品种的热爱而创立，专注于正确的体形、纯正的类型以及温顺配合的性情。\n\n场内每一匹马都受到悉心照料。我们欢迎与我们志趣相投的访客——无论您是在寻找下一位伙伴、配种机会，还是只是想认识这些马匹。",
            'phone' => '+20 100 000 0000',
            'whatsapp' => '201000000000',
            'email' => 'info@elbaraa-arabians.com',
            'address_en' => 'Egypt',
            'address_ar' => 'مصر',
            'address_zh' => '埃及',
            'facebook' => '',
            'instagram' => '',
        ]);
    }
}
