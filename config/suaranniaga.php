<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Produk dummy
    |--------------------------------------------------------------------------
    */

    'produk' => [
        [
            'id' => 1,
            'nama' => 'Beras Setra Ramos 5 kg',
            'ringkas' => 'Pulen, kemasan tertutup',
            'harga' => 72000,
            'stok' => 'Stok tersedia',
            'deskripsi' => 'Beras pulen dengan tekstur lembut. Dikemas dalam karung 5 kg yang tertutup rapat. Cocok untuk konsumsi sehari-hari.',
            'toko' => 'Toko Beras Sejahtera',
            'rating' => '4,8',
            'kota' => 'Bandung',
            'audio' => 'Beras Setra Ramos 5 kilogram. Harga tujuh puluh dua ribu rupiah. Stok tersedia. Beras pulen dengan tekstur lembut, dikemas dalam karung lima kilogram yang tertutup rapat.',
        ],
        [
            'id' => 2,
            'nama' => 'Beras Pandan Wangi 5 kg',
            'ringkas' => 'Aroma wangi, kualitas premium',
            'harga' => 89000,
            'stok' => 'Stok tersedia',
            'deskripsi' => 'Beras pandan wangi dengan aroma khas yang kuat. Kemasan rapi 5 kg, kualitas premium untuk hidangan harian.',
            'toko' => 'Toko Beras Sejahtera',
            'rating' => '4,7',
            'kota' => 'Bandung',
            'audio' => 'Beras Pandan Wangi 5 kilogram. Harga delapan puluh sembilan ribu rupiah. Aroma wangi, kualitas premium.',
        ],
        [
            'id' => 3,
            'nama' => 'Beras Medium 5 kg',
            'ringkas' => 'Pilihan ekonomis keluarga',
            'harga' => 65000,
            'stok' => 'Stok tersedia',
            'deskripsi' => 'Beras medium dengan harga terjangkau. Cocok untuk kebutuhan keluarga sehari-hari.',
            'toko' => 'Toko Beras Sejahtera',
            'rating' => '4,5',
            'kota' => 'Bandung',
            'audio' => 'Beras Medium 5 kilogram. Harga enam puluh lima ribu rupiah. Pilihan ekonomis keluarga.',
        ],
        [
            'id' => 4,
            'nama' => 'Minyak Goreng 2 L',
            'ringkas' => 'Kemasan besar, siap pakai',
            'harga' => 34000,
            'stok' => 'Stok tersedia',
            'deskripsi' => 'Minyak goreng kemasan 2 liter. Praktis untuk kebutuhan dapur harian.',
            'toko' => 'Toko Dapur Sejahtera',
            'rating' => '4,6',
            'kota' => 'Bandung',
            'audio' => 'Minyak Goreng 2 liter. Harga tiga puluh empat ribu rupiah. Kemasan besar, siap pakai.',
        ],
        [
            'id' => 5,
            'nama' => 'Telur Ayam 1 kg',
            'ringkas' => 'Segar, mudah diolah',
            'stok' => 'Stok tersedia',
            'harga' => 28000,
            'stok' => 'Stok tersedia',
            'deskripsi' => 'Telur ayam segar berat 1 kilogram. Diambil langsung dari peternak nearby.',
            'toko' => 'Toko Segar Bandung',
            'rating' => '4,9',
            'kota' => 'Bandung',
            'audio' => 'Telur Ayam 1 kilogram. Harga dua puluh delapan ribu rupiah. Segar dan mudah diolah.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Keranjang dummy
    |--------------------------------------------------------------------------
    */

    'keranjang' => [
        ['produk_id' => 1, 'jumlah' => 1],
        ['produk_id' => 4, 'jumlah' => 1],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pesanan dummy
    |--------------------------------------------------------------------------
    */

    'pesanan' => [
        [
            'id' => 'SN-2026-00124',
            'produk' => 'Beras Setra Ramos 5 kg',
            'status' => 'Pesanan baru',
            'helper' => 'Dibuat hari ini, menunggu diproses penjual.',
            'aksi' => 'Lihat detail',
        ],
        [
            'id' => 'SN-2026-00123',
            'produk' => 'Minyak Goreng 2 L',
            'status' => 'Menunggu konfirmasi',
            'helper' => 'Menunggu konfirmasi penjual.',
            'aksi' => 'Lihat detail',
        ],
        [
            'id' => 'SN-2026-00120',
            'produk' => 'Telur Ayam 1 kg',
            'status' => 'Dalam pengiriman',
            'helper' => 'Sedang diantar oleh kurir.',
            'aksi' => 'Lihat detail',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Aktivitas dashboard penjual
    |--------------------------------------------------------------------------
    */

    'aktivitas' => [
        ['id' => 'SN-00124', 'produk' => 'Beras Setra Ramos 5 kg', 'status' => 'Pesanan baru'],
        ['id' => 'SN-00123', 'produk' => 'Minyak Goreng 2 L', 'status' => 'Menunggu konfirmasi'],
        ['id' => 'SN-00120', 'produk' => 'Telur Ayam 1 kg', 'status' => 'Dalam pengiriman'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrik dashboard penjual
    |--------------------------------------------------------------------------
    */

    'ringkasan_toko' => [
        ['judul' => 'Pesanan baru', 'deskripsi' => '5 pesanan perlu diproses'],
        ['judul' => 'Produk aktif', 'deskripsi' => '18 produk tersedia'],
        ['judul' => 'Total penjualan', 'deskripsi' => 'Rp 2.450.000 bulan ini'],
    ],

];
