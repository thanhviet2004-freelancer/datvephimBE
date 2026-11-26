<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Showtime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $movies = [
            ['title' => 'Phía Bên Kia Chân Trời', 'genre' => 'Phiêu lưu · Khoa học', 'duration' => 125, 'rating' => 'P', 'art' => 'horizon', 'description' => 'Một tín hiệu từ hành tinh xa xôi đưa nhóm bạn trẻ vào chuyến hành trình vượt ngoài trí tưởng tượng. Giữa vũ trụ rộng lớn, họ tìm thấy điều quý giá nhất: lòng can đảm và tình bạn. Phim và nội dung minh họa dành cho dự án demo.'],
            ['title' => 'Điều Kỳ Diệu Đại Dương', 'genre' => 'Hoạt hình · Gia đình', 'duration' => 98, 'rating' => 'P', 'art' => 'ocean', 'description' => 'Theo chân chú cá nhỏ Mây khám phá thành phố dưới biển và tìm lại những rạn san hô đã mất. Một câu chuyện trong trẻo về tình bạn và tình yêu thiên nhiên. Phim minh họa dành cho dự án demo.'],
            ['title' => 'Mùa Hè Năm Ấy', 'genre' => 'Tình cảm · Gia đình', 'duration' => 110, 'rating' => 'P', 'art' => 'summer', 'description' => 'Trở về thị trấn nhỏ trong kỳ nghỉ hè, An cùng những người bạn cũ sửa lại rạp chiếu phim của ông ngoại. Những thước phim gắn kết hai thế hệ và lưu giữ một mùa hè đáng nhớ. Phim minh họa dành cho dự án demo.'],
            ['title' => 'Thành Phố Ngàn Sao', 'genre' => 'Phiêu lưu · Kỳ ảo', 'duration' => 116, 'rating' => 'P', 'art' => 'stars', 'description' => 'Khi những vì sao bỗng biến mất, hai chị em bắt đầu chuyến đi tới thành phố trên mây. Một cuộc phiêu lưu rực rỡ về ước mơ và niềm tin vào chính mình. Phim minh họa dành cho dự án demo.'],
        ];
        foreach ($movies as $index => $data) {
            $movie = Movie::updateOrCreate(['title' => $data['title']], $data);
            for ($day = 0; $day < 5; $day++) {
                foreach (['10:00', '14:00', '18:00', '21:00'] as $time) {
                    Showtime::firstOrCreate([
                        'room' => 'Phòng '.($index + 1),
                        'starts_at' => now()->startOfDay()->addDays($day)->setTimeFromTimeString($time),
                    ], ['movie_id' => $movie->id, 'price' => $time === '21:00' ? 90000 : 75000]);
                }
            }
        }
    }
}
