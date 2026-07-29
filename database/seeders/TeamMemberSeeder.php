<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TeamMember;

class TeamMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultMembers = [
            [
                'nama' => 'Jenny Wilson',
                'jabatan' => 'Senior Chef',
                'foto' => null,
                'urutan' => 1,
            ],
            [
                'nama' => 'Jane Cooper',
                'jabatan' => 'Worker',
                'foto' => null,
                'urutan' => 2,
            ],
            [
                'nama' => 'Cody Fisher',
                'jabatan' => 'Security Guard',
                'foto' => null,
                'urutan' => 3,
            ],
            [
                'nama' => 'Robert Fox',
                'jabatan' => 'Senior Recipe Manager',
                'foto' => null,
                'urutan' => 4,
            ],
        ];

        foreach ($defaultMembers as $member) {
            TeamMember::firstOrCreate(['nama' => $member['nama']], $member);
        }
    }
}
