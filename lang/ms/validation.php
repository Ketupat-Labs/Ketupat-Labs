<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'accepted' => ':attribute mesti diterima.',
    'active_url' => ':attribute bukan URL yang sah.',
    'after' => ':attribute mesti tarikh selepas :date.',
    'after_or_equal' => ':attribute mesti tarikh selepas atau sama dengan :date.',
<<<<<<< HEAD
    'alpha' => ':attribute hanya boleh mengandungi huruf.',
    'alpha_dash' => ':attribute hanya boleh mengandungi huruf, nombor, sengkang dan garis bawah.',
    'alpha_num' => ':attribute hanya boleh mengandungi huruf dan nombor.',
    'array' => ':attribute mesti jujukan.',
    'before' => ':attribute mesti tarikh sebelum :date.',
    'before_or_equal' => ':attribute mesti tarikh sebelum atau sama dengan :date.',
    'between' => [
        'numeric' => ':attribute mesti antara :min dan :max.',
        'file' => ':attribute mesti antara :min dan :max kilobait.',
        'string' => ':attribute mesti antara :min dan :max aksara.',
        'array' => ':attribute mesti mempunyai antara :min dan :max item.',
    ],
    'boolean' => 'Medan :attribute mesti benar atau palsu.',
    'confirmed' => 'Pengesahan :attribute tidak sepadan.',
    'date' => ':attribute bukan tarikh yang sah.',
    'date_equals' => ':attribute mesti tarikh sama dengan :date.',
=======
    'alpha' => ':attributeengan :date.',
>>>>>>> 6d44d3eac56b827c0904d252e11f4532a06f0633
    'date_format' => ':attribute tidak sepadan dengan format :format.',
    'different' => ':attribute dan :other mesti berbeza.',
    'digits' => ':attribute mesti :digits digit.',
    'digits_between' => ':attribute mesti antara :min dan :max digit.',
    'dimensions' => ':attribute mempunyai dimensi imej yang tidak sah.',
    'distinct' => 'Medan :attribute mempunyai nilai pendua.',
    'email' => ':attribute mesti alamat e-mel yang sah.',
    'ends_with' => ':attribute mesti berakhir dengan salah satu daripada: :values.',
    'exists' => ':attribute yang dipilih tidak sah.',
    'file' => ':attribute mesti fail.',
    'filled' => 'Medan :attribute mesti mempunyai nilai.',
    'gt' => [
        'numeric' => ':attribute mesti lebih besar daripada :value.',
        'file' => ':attribute mesti lebih besar daripada :value kilobait.',
        'string' => ':attribute mesti lebih besar daripada :value aksara.',
        'array' => ':attribute mesti mempunyai lebih daripada :value item.',
    ],
    'gte' => [
        'numeric' => ':attribute mesti lebih besar atau sama dengan :value.',
        'file' => ':attribute mesti lebih besar atau sama dengan :value kilobait.',
        'string' => ':attribute mesti lebih besar atau sama dengan :value aksara.',
        'array' => ':attribute mesti mempunyai :value item atau lebih.',
    ],
    'image' => ':attribute mesti imej.',
    'in' => ':attribute yang dipilih tidak sah.',
    'in_array' => 'Medan :attribute tidak wujud dalam :other.',
    'integer' => ':attribute mesti integer.',
    'ip' => ':attribute mesti alamat IP yang sah.',
    'ipv4' => ':attribute mesti alamat IPv4 yang sah.',
    'ipv6' => ':attribute mesti alamat IPv6 yang sah.',
    'json' => ':attribute mesti rentetan JSON yang sah.',
    'lt' => [
        'numeric' => ':attribute mesti kurang daripada :value.',
        'file' => ':attribute mesti kurang daripada :value kilobait.',
        'string' => ':attribute mesti kurang daripada :value aksara.',
        'array' => ':attribute mesti mempunyai kurang daripada :value item.',
    ],
    'lte' => [
        'numeric' => ':attribute mesti kurang atau sama dengan :value.',
        'file' => ':attribute mesti kurang atau sama dengan :value kilobait.',
        'string' => ':attribute mesti kurang atau sama dengan :value aksara.',
        'array' => ':attribute tidak boleh mempunyai lebih daripada :value item.',
    ],
    'max' => [
        'numeric' => ':attribute tidak boleh lebih besar daripada :max.',
        'file' => ':attribute tidak boleh lebih besar daripada :max kilobait.',
        'string' => ':attribute tidak boleh lebih besar daripada :max aksara.',
        'array' => ':attribute tidak boleh mempunyai lebih daripada :max item.',
    ],
    'mimes' => ':attribute mesti fail jenis: :values.',
    'mimetypes' => ':attribute mesti fail jenis: :values.',
    'min' => [
        'numeric' => ':attribute mesti sekurang-kurangnya :min.',
        'file' => ':attribute mesti sekurang-kurangnya :min kilobait.',
        'string' => ':attribute mesti sekurang-kurangnya :min aksara.',
        'array' => ':attribute mesti mempunyai sekurang-kurangnya :min item.',
    ],
    'not_in' => ':attribute yang dipilih tidak sah.',
    'not_regex' => 'Format :attribute tidak sah.',
    'numeric' => ':attribute mesti nombor.',
    'password' => 'Kata laluan tidak betul.',
    'present' => 'Medan :attribute mesti ada.',
    'regex' => 'Format :attribute tidak sah.',
    'required' => 'Medan :attribute diperlukan.',
    'required_if' => 'Medan :attribute diperlukan apabila :other ialah :value.',
    'required_unless' => 'Medan :attribute diperlukan melainkan :other ada dalam :values.',
    'required_with' => 'Medan :attribute diperlukan apabila :values ada.',
    'required_with_all' => 'Medan :attribute diperlukan apabila :values ada.',
    'required_without' => 'Medan :attribute diperlukan apabila :values tiada.',
    'required_without_all' => 'Medan :attribute diperlukan apabila tiada :values ada.',
    'same' => ':attribute dan :other mesti sepadan.',
    'size' => [
        'numeric' => ':attribute mesti :size.',
        'file' => ':attribute mesti :size kilobait.',
        'string' => ':attribute mesti :size aksara.',
        'array' => ':attribute mesti mengandungi :size item.',
    ],
    'starts_with' => ':attribute mesti bermula dengan salah satu daripada: :values.',
    'string' => ':attribute mesti rentetan.',
    'timezone' => ':attribute mesti zon yang sah.',
    'unique' => ':attribute telah diambil.',
    'uploaded' => ':attribute gagal dimuat naik.',
    'url' => 'Format :attribute tidak sah.',
    'uuid' => ':attribute mesti UUID yang sah.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'mesej-tersuai',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'email' => 'e-mel',
        'password' => 'kata laluan',
        'title' => 'tajuk',
        'name' => 'nama',
        'description' => 'penerangan',
        'content' => 'kandungan',
        'date' => 'tarikh',
        'time' => 'masa',
        'type' => 'jenis',
        'status' => 'status',
        'class_id' => 'kelas',
        'lesson_id' => 'pelajaran',
        'activity_id' => 'aktiviti',
        'due_date' => 'tarikh akhir',
    ],
];
